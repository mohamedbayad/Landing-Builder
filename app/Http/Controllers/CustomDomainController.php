<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CustomDomain;
use App\Models\Landing;
use App\Services\DomainVerificationService;
use Illuminate\Support\Str;

class CustomDomainController extends Controller
{
    public function index()
    {
        $domains = CustomDomain::where('user_id', auth()->id())
                               ->with('landing') // CustomDomain model needs to use 'landing' relationship, will fix if needed
                               ->latest()
                               ->get();
        return view('dashboard.domains.index', compact('domains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => ['required', 'string', 'max:255', 'unique:custom_domains,domain',
                'regex:/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/'],
        ]);
        
        $domain = CustomDomain::create([
            'user_id'            => auth()->id(),
            'domain'             => strtolower($request->domain),
            'verification_token' => Str::random(32),
            'status'             => 'pending',
        ]);
        
        return redirect()->route('domains.show', $domain)
                         ->with('success', 'Domain added. Follow the DNS instructions below.');
    }

    public function show(CustomDomain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);
        
        // Get landings via workspace
        $landingPages = Landing::whereHas('workspace', function($q) {
            $q->where('user_id', auth()->id());
        })->get();
        
        return view('dashboard.domains.show', compact('domain', 'landingPages'));
    }

    public function verify(CustomDomain $domain, DomainVerificationService $service)
    {
        abort_if($domain->user_id !== auth()->id(), 403);
        
        $verified = $service->verify($domain);
        
        return back()->with(
            $verified ? 'success' : 'error',
            $verified ? 'Domain verified successfully!' : $domain->fresh()->error_message
        );
    }

    public function assign(Request $request, CustomDomain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);
        $request->validate(['landing_page_id' => 'required|exists:landings,id']);
        
        $landingPage = Landing::where('id', $request->landing_page_id)
                              ->whereHas('workspace', function($q) {
                                  $q->where('user_id', auth()->id());
                              })
                              ->firstOrFail();
        
        $domain->update(['landing_page_id' => $landingPage->id]);
        
        return back()->with('success', 'Domain assigned to landing page successfully!');
    }

    public function destroy(CustomDomain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);
        $domain->delete();
        return redirect()->route('domains.index')->with('success', 'Domain removed.');
    }
}
