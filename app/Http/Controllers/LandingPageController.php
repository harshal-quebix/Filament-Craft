<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class LandingPageController extends Controller
{
    public function index()
    {
        if (!file_exists(storage_path('installed'))) {
            return redirect(url('/install'));
        }

        return view('landing');
    }

    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        ContactUs::create($validated);

        return back()->with('success', __('Thank you for contacting us! We will get back to you soon.'));
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function guide()
    {
        return view('pages.guide');
    }

    public function dynamicPage($slug)
    {
        $menu = Menu::where('slug', $slug)
            ->where('page_type', 'content')
            ->where('is_active', true)
            ->firstOrFail();

        return view('pages.dynamic', compact('menu'));
    }
}
