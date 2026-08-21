<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\LandingCmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SaaSController extends Controller
{
    public function landing()
    {
        $cms = LandingCmsService::getAll();
        $plans = Plan::where('active', true)->orderBy('sort_order')->get();
        return view('saas.landing', compact('plans', 'cms'));
    }

    public function pricing()
    {
        $cms = LandingCmsService::getAll();
        $plans = Plan::where('active', true)->orderBy('sort_order')->get();
        return view('saas.pricing', compact('plans', 'cms'));
    }

    public function features()
    {
        $cms = LandingCmsService::getAll();
        return view('saas.features', compact('cms'));
    }

    public function contact()
    {
        $cms = LandingCmsService::getAll();
        return view('saas.contact', compact('cms'));
    }

    public function demoRequest()
    {
        $cms = LandingCmsService::getAll();
        return view('saas.demo', compact('cms'));
    }

    public function storeDemoRequest(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'estimated_students' => 'required|integer',
            'message' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\Log::info('Demo Request Submitted:', $request->all());

        return back()->with('pop_success', 'Thank you! Your demo request has been submitted. Our team will contact you shortly.');
    }
}
