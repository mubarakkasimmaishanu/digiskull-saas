<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production tenant seeder.
 *
 * Runs against a NEWLY created tenant database at provisioning time.
 * Seeds ONLY reference / lookup data — never demo users, staff, students,
 * or classes. The real school owner is inserted by
 * TenantProvisionService::createOwnerUser afterwards; the school then
 * builds its own classes, sections, staff, and students through the UI.
 *
 * This exists as the counterpart to DatabaseSeeder (which is a full
 * fixture seeder useful for local dev and demos, and DANGEROUS in prod
 * because it seeds accounts like superadmin/superadmin, student/student).
 *
 * Wired into Stancl via config/tenancy.php:seeder_parameters.--class.
 */
class DatabaseSeederProduction extends Seeder
{
    public function run(): void
    {
        // --- Universal reference data (geography, timezones, biology) ---
        $this->call(NationalitiesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(LgasTableSeeder::class);
        $this->call(BloodGroupsTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(TimezonesTableSeeder::class);

        // --- Tenancy config lookups required for the app to boot ---
        // These are pure configuration — no PII, no operational records.
        $this->call(TenancyExamCategoriesTableSeeder::class);
        $this->call(TenancyUserTypesTableSeeder::class);   // Role catalogue.
        $this->call(TenancySkillsTableSeeder::class);      // Report-card skill catalogue.
        $this->call(TenancySettingsTableSeeder::class);    // Site settings defaults.
        $this->call(TenancyClassTypesTableSeeder::class);  // Primary / Secondary / etc.

        // DELIBERATELY NOT SEEDED in production:
        //   TenancyUsersTableSeeder          — demo accounts with known creds.
        //   TenancyStaffRecordsTableSeeder   — depends on demo users.
        //   TenancyStudentRecordsTableSeeder — demo student.
        //   ParentRelativesTableSeeder       — demo parent.
        //   TenancyMyClassesTableSeeder      — school-specific; admin creates.
        //   TenancySectionsTableSeeder       — depends on MyClasses.
    }
}
