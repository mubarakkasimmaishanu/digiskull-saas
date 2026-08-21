<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Setting;
use App\Services\LandingCmsService;
use App\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LandingCmsManagementTest extends TestCase
{
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure tenancy is ended so we run in central context
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }

        $this->withoutMiddleware([
            \App\Http\Middleware\Custom\CheckForPasswordUpdate::class,
            \PragmaRX\Google2FALaravel\Middleware::class,
        ]);

        $this->adminUser = User::where('user_type', 'it_guy')->first();
        if (!$this->adminUser) {
            $this->adminUser = User::create([
                'name' => 'System Owner Admin',
                'username' => 'sysowner_' . uniqid(),
                'user_type' => 'it_guy',
                'email' => 'admin_test_cms_' . uniqid() . '@digiskul.app',
                'password' => Hash::make('password123'),
                'code' => 'ADMIN_' . strtoupper(uniqid()),
                'religion' => 'Islam',
                'dob' => '01/01/1990',
                'gender' => 'Male',
                'address' => 'Central Office',
                'phone' => '+2348000000000',
                'password_updated_at' => now(),
                'email_verified_at' => now(),
            ]);
        } else {
            $this->adminUser->update(['password_updated_at' => now()]);
        }
    }

    public function test_landing_cms_dashboard_accessible_by_system_owner()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('saas.admin.landing_cms'));

        $response->assertStatus(200);
        $response->assertSee('Landing Page &amp; SaaS CMS Dynamic Management', false);
        $response->assertSee('Hero &amp; Stats', false);
        $response->assertSee('Pricing Plans', false);
    }

    public function test_system_owner_can_dynamically_update_hero_section()
    {
        $payload = [
            'hero_badge' => 'TESTING DYNAMIC CMS BADGE 2026',
            'hero_title' => 'The Ultra High-Performance School Cloud ERP',
            'hero_subtitle' => 'Custom dynamic subtitle updated directly by the super administrator.',
            'hero_cta_text' => 'Claim Your 1-Term Trial',
            'hero_cta_sub' => '100% Free · No Initial Charge',
            'demo_cta_text' => 'Request Live Walkthrough',
            'stat_schools' => '350+ Institutions',
            'stat_students' => '90,000+ Enrolled',
            'stat_uptime' => '99.99%',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('saas.admin.landing_cms.hero'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('pop_success');

        // Check that public landing page renders the new values
        $landing = $this->get(route('saas.landing'));
        $landing->assertStatus(200);
        $landing->assertSee('TESTING DYNAMIC CMS BADGE 2026');
        $landing->assertSee('The Ultra High-Performance School Cloud ERP');
        $landing->assertSee('Custom dynamic subtitle updated directly by the super administrator.');
        $landing->assertSee('Claim Your 1-Term Trial');
    }

    public function test_system_owner_can_dynamically_update_faqs_and_testimonials()
    {
        $faqPayload = [
            'faqs' => [
                ['q' => 'Can we customize the grading scale?', 'a' => 'Yes, full support for WAEC, NECO, and Cambridge scales.'],
                ['q' => 'What is the trial period?', 'a' => '1 full academic term (90 days) free of charge.'],
            ]
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('saas.admin.landing_cms.faqs'), $faqPayload);

        $response->assertRedirect();
        $response->assertSessionHas('pop_success');

        $landing = $this->get(route('saas.landing'));
        $landing->assertStatus(200);
        $landing->assertSee('Can we customize the grading scale?');
        $landing->assertSee('Yes, full support for WAEC, NECO, and Cambridge scales.');
    }

    public function test_system_owner_can_update_plan_prices_and_limits()
    {
        $this->withoutExceptionHandling();

        $plan = Plan::first();
        if (!$plan) {
            $plan = Plan::create([
                'name' => 'Custom Enterprise Tier',
                'slug' => 'custom_tier_' . uniqid(),
                'termly_price' => 95000,
                'yearly_price' => 250000,
                'monthly_price' => 25000,
                'max_students' => 800,
                'max_staff' => 75,
                'active' => 1,
            ]);
        }

        $response = $this->actingAs($this->adminUser)
            ->post(route('saas.admin.landing_cms.plan', ['plan_id' => $plan->id]), [
                'name' => 'Custom Tier Updated',
                'termly_price' => 110000,
                'yearly_price' => 290000,
                'max_students' => 1200,
                'max_staff' => 100,
                'active' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('pop_success');

        $plan->refresh();
        $this->assertEquals('Custom Tier Updated', $plan->name);
        $this->assertEquals(110000, $plan->termly_price);
        $this->assertEquals(1200, $plan->max_students);
    }
}
