<?php

namespace App\Http\Controllers;

use App\Models\EmailContact;
use App\Models\EmailEvent;
use App\Models\EmailLink;
use App\Models\EmailMessage;
use App\Services\Email\EmailContactService;
use App\Services\Email\EmailTrackingService;
use Illuminate\Http\Request;

class EmailTrackingController extends Controller
{
    public function open(EmailMessage $message, string $hash, EmailTrackingService $trackingService)
    {
        $expected = $trackingService->buildTrackingHash($message);
        if (!hash_equals($expected, $hash)) {
            return $this->pixelResponse();
        }

        if (!$message->opened_at) {
            $message->opened_at = now();
            if (in_array($message->status, ['queued', 'sent', 'delivered'], true)) {
                $message->status = 'opened';
            }
            $message->save();

            if ($message->contact_id) {
                EmailContact::query()
                    ->whereKey($message->contact_id)
                    ->update(['last_opened_at' => now()]);
            }
        }

        EmailEvent::create([
            'email_message_id' => $message->id,
            'event_type' => 'opened',
            'event_data' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
            'occurred_at' => now(),
        ]);

        return $this->pixelResponse();
    }

    public function click(string $code)
    {
        $link = EmailLink::query()->where('tracking_code', $code)->firstOrFail();
        $message = $link->message;

        $now = now();
        $link->update([
            'total_clicks' => $link->total_clicks + 1,
            'first_clicked_at' => $link->first_clicked_at ?: $now,
            'last_clicked_at' => $now,
        ]);

        if ($message) {
            $message->update([
                'first_clicked_at' => $message->first_clicked_at ?: $now,
                'status' => 'clicked',
            ]);

            if ($message->contact_id) {
                EmailContact::query()
                    ->whereKey($message->contact_id)
                    ->update(['last_clicked_at' => $now]);
            }

            EmailEvent::create([
                'email_message_id' => $message->id,
                'event_type' => 'clicked',
                'event_data' => [
                    'url' => $link->original_url,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
                'occurred_at' => now(),
            ]);
        }

        return redirect()->away($link->original_url);
    }

    public function unsubscribe(Request $request, EmailContact $contact, EmailContactService $contactService)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        if ($request->filled('email') && strcasecmp($request->query('email'), $contact->email) !== 0) {
            abort(403);
        }

        $contactService->markUnsubscribed($contact, reason: 'unsubscribe_link', source: 'email_link');

        return view('email-automation.public.unsubscribed', compact('contact'));
    }

    private function pixelResponse()
    {
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}

