<?php

namespace App\Http\Controllers;

use App\Models\EmailContact;
use App\Services\Email\EmailContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmailContactController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailContact::query()
            ->where('user_id', Auth::id())
            ->withCount('messages');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($inner) use ($search) {
                $inner->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $contacts = $query->latest()->paginate(25)->withQueryString();

        return view('email-automation.contacts.index', compact('contacts'));
    }

    public function show(EmailContact $contact)
    {
        $this->authorizeContact($contact);

        $contact->load([
            'messages' => fn ($query) => $query->latest()->limit(50),
            'messages.events' => fn ($query) => $query->latest()->limit(5),
        ]);

        return view('email-automation.contacts.show', compact('contact'));
    }

    public function updateStatus(Request $request, EmailContact $contact, EmailContactService $contactService)
    {
        $this->authorizeContact($contact);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['subscribed', 'unsubscribed', 'bounced', 'complained'])],
        ]);

        if ($validated['status'] === 'unsubscribed') {
            $contactService->markUnsubscribed($contact, reason: 'manual_update', source: 'dashboard');
        } else {
            $contact->update(['status' => $validated['status']]);
        }

        return redirect()->route('email-automation.contacts.show', $contact)
            ->with('success', 'Contact status updated.');
    }

    private function authorizeContact(EmailContact $contact): void
    {
        if ($contact->user_id !== Auth::id()) {
            abort(403);
        }
    }
}

