<?php

namespace App\Http\Controllers;

use App\Models\EmailAutomation;
use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailMessage::query()
            ->where('user_id', Auth::id())
            ->with(['automation:id,name', 'template:id,name', 'contact:id,email,first_name,last_name']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('automation_id')) {
            $query->where('automation_id', $request->integer('automation_id'));
        }

        if ($request->filled('template_id')) {
            $query->where('template_id', $request->integer('template_id'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($inner) use ($search) {
                $inner->where('recipient_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->latest()->paginate(30)->withQueryString();
        $automations = EmailAutomation::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name']);
        $templates = EmailTemplate::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('email-automation.activity.index', compact('messages', 'automations', 'templates'));
    }
}

