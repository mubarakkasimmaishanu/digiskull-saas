<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class LandingCmsService
{
    const CACHE_KEY = 'digiskul_landing_cms_settings';

    /**
     * Return default configuration values for all landing page sections.
     */
    public static function defaults(): array
    {
        return [
            // Hero
            'hero_badge' => 'CLOUD SCHOOL ERP · 1-TERM FREE TRIAL',
            'hero_title' => 'Next-Generation Cloud ERP for Modern Schools',
            'hero_subtitle' => 'From continuous assessments (CA 1, CA 2, Exams) and automated WAEC grading to bursary fee lockdown, daily attendance registers, and parent SMS broadcasts — all in one blazing-fast, dedicated cloud portal.',
            'hero_cta_text' => 'Start 1-Term Free Trial',
            'hero_cta_sub' => '1-Term Free · ₦0 Today',
            'demo_cta_text' => 'Book Live Demo',
            
            // Statistics
            'stat_schools' => '150+',
            'stat_schools_label' => 'Active Schools',
            'stat_students' => '45,000+',
            'stat_students_label' => 'Enrolled Students',
            'stat_uptime' => '99.9%',
            'stat_uptime_label' => 'Cloud Uptime SLA',
            'stat_accuracy' => '100%',
            'stat_accuracy_label' => 'Computation Accuracy',

            // Accreditation Badges
            'curriculum_badges' => [
                ['name' => 'WAEC Scale Standard', 'icon' => 'verified'],
                ['name' => 'NECO Standard', 'icon' => 'verified'],
                ['name' => 'Junior & Senior Secondary', 'icon' => 'school'],
                ['name' => '100% Isolated Database', 'icon' => 'database'],
            ],

            // Core Pillars / Features
            'features' => [
                [
                    'title' => 'Continuous Assessment Workflow',
                    'desc' => 'Multi-tier CA score entry (Test 1, Test 2, Exams) with automated total calculations and teacher moderation gates.',
                    'icon' => 'edit_note',
                    'color' => 'info'
                ],
                [
                    'title' => 'Bursary Fee Lockdown',
                    'desc' => 'Automated invoice generation with strict exam and terminal report card lockdown for un-settled balances.',
                    'icon' => 'account_balance_wallet',
                    'color' => 'warning'
                ],
                [
                    'title' => 'Broad-Sheets & Report Cards',
                    'desc' => 'One-click computation of class positions, grade rankings, GPA, cognitive remarks, and printable official transcripts.',
                    'icon' => 'assignment',
                    'color' => 'success'
                ],
                [
                    'title' => 'Daily Attendance & SMS',
                    'desc' => 'Sleek attendance marking with instant statistical percentage and bulk SMS notifications to parents.',
                    'icon' => 'fact_check',
                    'color' => 'primary'
                ],
            ],

            // Comparison Table
            'comparison' => [
                ['feature' => 'Database Architecture', 'traditional' => 'Shared database table (High risk of data leakage)', 'digiskul' => '100% Dedicated isolated tenant database schema'],
                ['feature' => 'Assessment Grading', 'traditional' => 'Manual paper or fragmented Excel calculations', 'digiskul' => 'Automated WAEC/NECO scale conversion & class positions'],
                ['feature' => 'Financial Lockdown', 'traditional' => 'Manual cross-referencing at exam gates', 'digiskul' => 'Automated report card lock until fees are paid'],
                ['feature' => 'Teacher Access Scoping', 'traditional' => 'Open master spreadsheets prone to edits', 'digiskul' => 'Strict role-based limits per subject & assigned class'],
                ['feature' => 'Parent Communication', 'traditional' => 'Paper letters lost in transit', 'digiskul' => 'Direct SMS fee reminders & termly report card portal'],
            ],

            // Testimonials
            'testimonials' => [
                [
                    'name' => 'Alhaji Mansur Bello',
                    'role' => 'Principal, Al-Huda Academy, Kaduna',
                    'quote' => 'Digiskul eliminated three weeks of end-of-term broad-sheet chaos. Exam score compilation and report card generation now happen within 30 minutes with zero math errors.',
                    'rating' => 5
                ],
                [
                    'name' => 'Mrs. Folashade Adeyemi',
                    'role' => 'Director of Studies, Crestview High, Abuja',
                    'quote' => 'The bursary fee lockdown feature alone increased our tuition collection rate to 98% before the examination week. Highly recommended for every serious school.',
                    'rating' => 5
                ],
                [
                    'name' => 'Dr. Emmanuel Okoro',
                    'role' => 'Proprietor, Heritage Model Schools, Kano',
                    'quote' => 'The 1-term free trial gave us complete confidence to test with real student records. The isolated database architecture provides unmatched speed and peace of mind.',
                    'rating' => 5
                ],
            ],

            // FAQs
            'faqs' => [
                [
                    'q' => 'How does the 1-Term Free Trial work?',
                    'a' => 'You get 1 full academic term (90 days) of completely free access to all core ERP modules with ₦0 charged today. You provide payment details at registration; automated termly or yearly billing begins only after your trial period ends, and you can cancel anytime before it expires.'
                ],
                [
                    'q' => 'How quickly can our school start using the portal?',
                    'a' => 'Instantly! Completing the 5-step registration wizard provisions your school’s unique web address (e.g. greenwood.localhost:8000) with dedicated Super Administrator credentials immediately without technical delays.'
                ],
                [
                    'q' => 'How does the Report Card Financial Restriction work?',
                    'a' => 'When an accountant issues an invoice, the system monitors payment status. If a student has an outstanding balance, their terminal report card is automatically locked with a settlement notice. Once the bursar records the payment, the report card unlocks immediately.'
                ],
                [
                    'q' => 'Is our school data safe and isolated?',
                    'a' => 'Yes! Every school operates on a dedicated, isolated database schema protected by 256-bit SSL encryption, automated backups, and Two-Factor Authentication (2FA).'
                ],
            ],

            // Contact & Footer
            'contact_email' => 'support@digiskul.app',
            'contact_phone' => '+234 800 000 0000',
            'contact_whatsapp' => '+234 800 000 0000',
            'contact_address' => 'No. 183 Tukur-Tukur, Zaria, Kaduna State, Nigeria',
            'footer_copyright' => '© ' . date('Y') . ' Digiskul. All rights reserved.',
        ];
    }

    /**
     * Get all merged settings (defaults + DB saved values).
     */
    public static function getAll(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $defaults = self::defaults();
            $saved = Setting::where('type', 'like', 'cms_%')->pluck('description', 'type')->toArray();

            $merged = [];
            foreach ($defaults as $key => $defaultVal) {
                $settingKey = 'cms_' . $key;
                if (isset($saved[$settingKey]) && $saved[$settingKey] !== null) {
                    $raw = $saved[$settingKey];
                    // If default was array/json, decode it
                    if (is_array($defaultVal)) {
                        $decoded = json_decode($raw, true);
                        $merged[$key] = is_array($decoded) ? $decoded : $defaultVal;
                    } else {
                        $merged[$key] = $raw;
                    }
                } else {
                    $merged[$key] = $defaultVal;
                }
            }

            return $merged;
        });
    }

    /**
     * Update CMS settings and bust cache.
     */
    public static function saveSettings(array $data): void
    {
        foreach ($data as $key => $val) {
            $settingKey = 'cms_' . $key;
            $serialized = is_array($val) ? json_encode($val) : $val;

            Setting::updateOrCreate(
                ['type' => $settingKey],
                ['description' => $serialized]
            );
        }

        Cache::forget(self::CACHE_KEY);
    }
}
