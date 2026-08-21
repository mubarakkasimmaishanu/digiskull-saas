<?php

namespace App\Http\Controllers\ItGuy;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\LandingCmsService;
use Illuminate\Http\Request;

class LandingCmsController extends Controller
{
    public function index()
    {
        $cms = LandingCmsService::getAll();
        $plans = Plan::orderBy('sort_order')->get();

        return view('pages.it_guy.landing_cms.index', compact('cms', 'plans'));
    }

    public function updateHero(Request $request)
    {
        $request->validate([
            'hero_badge' => 'required|string|max:255',
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'hero_cta_text' => 'required|string|max:100',
            'hero_cta_sub' => 'required|string|max:100',
            'demo_cta_text' => 'required|string|max:100',
            'stat_schools' => 'required|string|max:50',
            'stat_students' => 'required|string|max:50',
            'stat_uptime' => 'required|string|max:50',
        ]);

        LandingCmsService::saveSettings([
            'hero_badge' => $request->hero_badge,
            'hero_title' => $request->hero_title,
            'hero_subtitle' => $request->hero_subtitle,
            'hero_cta_text' => $request->hero_cta_text,
            'hero_cta_sub' => $request->hero_cta_sub,
            'demo_cta_text' => $request->demo_cta_text,
            'stat_schools' => $request->stat_schools,
            'stat_students' => $request->stat_students,
            'stat_uptime' => $request->stat_uptime,
        ]);

        return back()->with('pop_success', 'Hero and stats sections updated successfully!');
    }

    public function updateFeatures(Request $request)
    {
        $features = [];
        if ($request->has('features') && is_array($request->features)) {
            foreach ($request->features as $item) {
                if (!empty($item['title'])) {
                    $features[] = [
                        'title' => $item['title'],
                        'desc' => $item['desc'] ?? '',
                        'icon' => $item['icon'] ?? 'star',
                        'color' => $item['color'] ?? 'info',
                    ];
                }
            }
        }

        LandingCmsService::saveSettings(['features' => $features]);

        return back()->with('pop_success', 'Core feature highlights updated successfully!');
    }

    public function updateComparison(Request $request)
    {
        $comparison = [];
        if ($request->has('comparison') && is_array($request->comparison)) {
            foreach ($request->comparison as $item) {
                if (!empty($item['feature'])) {
                    $comparison[] = [
                        'feature' => $item['feature'],
                        'traditional' => $item['traditional'] ?? '',
                        'digiskul' => $item['digiskul'] ?? '',
                    ];
                }
            }
        }

        LandingCmsService::saveSettings(['comparison' => $comparison]);

        return back()->with('pop_success', 'Comparison table updated successfully!');
    }

    public function updateTestimonials(Request $request)
    {
        $testimonials = [];
        if ($request->has('testimonials') && is_array($request->testimonials)) {
            foreach ($request->testimonials as $item) {
                if (!empty($item['name']) && !empty($item['quote'])) {
                    $testimonials[] = [
                        'name' => $item['name'],
                        'role' => $item['role'] ?? '',
                        'quote' => $item['quote'],
                        'rating' => (int)($item['rating'] ?? 5),
                    ];
                }
            }
        }

        LandingCmsService::saveSettings(['testimonials' => $testimonials]);

        return back()->with('pop_success', 'Testimonials and social proof updated successfully!');
    }

    public function updateFaqs(Request $request)
    {
        $faqs = [];
        if ($request->has('faqs') && is_array($request->faqs)) {
            foreach ($request->faqs as $item) {
                if (!empty($item['q']) && !empty($item['a'])) {
                    $faqs[] = [
                        'q' => $item['q'],
                        'a' => $item['a'],
                    ];
                }
            }
        }

        LandingCmsService::saveSettings(['faqs' => $faqs]);

        return back()->with('pop_success', 'FAQ items updated successfully!');
    }

    public function updateContact(Request $request)
    {
        $request->validate([
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            'contact_whatsapp' => 'nullable|string|max:50',
            'contact_address' => 'required|string|max:500',
            'footer_copyright' => 'required|string|max:255',
        ]);

        LandingCmsService::saveSettings([
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'contact_whatsapp' => $request->contact_whatsapp,
            'contact_address' => $request->contact_address,
            'footer_copyright' => $request->footer_copyright,
        ]);

        return back()->with('pop_success', 'Contact information & footer updated successfully!');
    }

    public function updatePlan(Request $request, $plan_id)
    {
        $plan = Plan::find($plan_id);
        if (!$plan && is_string($plan_id)) {
            $decoded = \App\Helpers\Qs::decodeHash($plan_id);
            $plan = Plan::find($decoded);
        }
        if (!$plan) {
            $plan = Plan::where('slug', $plan_id)->first();
        }
        if (!$plan) {
            abort(404, "Plan not found with identifier [{$plan_id}]");
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'termly_price' => 'required|numeric|min:0',
            'yearly_price' => 'required|numeric|min:0',
            'max_students' => 'required|integer|min:1',
            'max_staff' => 'required|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        $plan->update([
            'name' => $request->name,
            'termly_price' => $request->termly_price,
            'yearly_price' => $request->yearly_price,
            'max_students' => $request->max_students,
            'max_staff' => $request->max_staff,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return back()->with('pop_success', "Plan '{$plan->name}' updated successfully!");
    }

    public function resetDefaults()
    {
        LandingCmsService::saveSettings(LandingCmsService::defaults());
        return back()->with('pop_success', 'Landing page settings restored to defaults!');
    }
}
