<?php

namespace App\Services\Email;

use App\Models\EmailContact;
use App\Models\EmailLink;
use App\Models\EmailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EmailTrackingService
{
    public function trackingBaseUrl(): string
    {
        try {
            $request = request();
            $root = trim((string) $request?->getSchemeAndHttpHost());
            if ($root !== '') {
                return rtrim($root, '/');
            }
        } catch (\Throwable $e) {
            // Request may not be bound in CLI/queue context.
        }

        return rtrim((string) config('app.url'), '/');
    }

    public function isLikelyPublicBaseUrl(): bool
    {
        $url = $this->trackingBaseUrl();
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (str_starts_with($host, '10.')
                || str_starts_with($host, '192.168.')
                || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $host)
            ) {
                return false;
            }
        }

        return true;
    }

    public function prepareTrackedHtml(EmailMessage $message, string $html, ?EmailContact $contact = null, ?string $customFooter = null): string
    {
        $tracked = $this->replaceLinksWithTracking($message, $html);
        $tracked = $this->appendFooter($tracked, $contact, $customFooter);

        return $this->appendOpenPixel($message, $tracked);
    }

    public function buildTrackingHash(EmailMessage $message): string
    {
        return hash_hmac('sha256', $message->id.'|'.$message->recipient_email, (string) config('app.key'));
    }

    public function buildUnsubscribeUrl(EmailContact $contact): string
    {
        return URL::temporarySignedRoute(
            'email.unsubscribe',
            now()->addYears(10),
            ['contact' => $contact->id, 'email' => $contact->email]
        );
    }

    private function replaceLinksWithTracking(EmailMessage $message, string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $wrapped = '<!DOCTYPE html><html><body>'.$html.'</body></html>';
        $dom->loadHTML(mb_convert_encoding($wrapped, 'HTML-ENTITIES', 'UTF-8'));

        foreach ($dom->getElementsByTagName('a') as $anchor) {
            $href = trim((string) $anchor->getAttribute('href'));
            if (!$this->isTrackableUrl($href)) {
                continue;
            }

            $code = Str::random(40);
            EmailLink::create([
                'email_message_id' => $message->id,
                'original_url' => $href,
                'tracking_code' => $code,
            ]);

            $anchor->setAttribute('href', route('email.click', ['code' => $code]));
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $output = '';

        if ($body) {
            foreach ($body->childNodes as $child) {
                $output .= $dom->saveHTML($child);
            }
        } else {
            $output = $html;
        }

        libxml_clear_errors();

        return $output;
    }

    private function appendFooter(string $html, ?EmailContact $contact = null, ?string $customFooter = null): string
    {
        if (!$contact) {
            return $html;
        }

        $footerBody = trim((string) $customFooter);
        if ($footerBody === '') {
            $footerBody = 'You are receiving this email because you interacted with our funnel.';
        }

        $unsubscribeUrl = $this->buildUnsubscribeUrl($contact);
        $footer = '<div style="margin-top:24px;font-size:12px;color:#666;">'
            .e($footerBody)
            .' <a href="'.e($unsubscribeUrl).'">Unsubscribe</a></div>';

        return $html.$footer;
    }

    private function appendOpenPixel(EmailMessage $message, string $html): string
    {
        $pixelUrl = route('email.track.open', [
            'message' => $message->id,
            'hash' => $this->buildTrackingHash($message),
        ]);

        $pixel = '<img src="'.e($pixelUrl).'" alt="" width="1" height="1" style="display:none !important;" />';
        return $html.$pixel;
    }

    private function isTrackableUrl(string $href): bool
    {
        if ($href === '' || str_starts_with($href, '#')) {
            return false;
        }

        if (str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')) {
            return false;
        }

        return filter_var($href, FILTER_VALIDATE_URL) && in_array(parse_url($href, PHP_URL_SCHEME), ['http', 'https'], true);
    }
}
