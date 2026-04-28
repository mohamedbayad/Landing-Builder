<?php

namespace App\Services\Email;

use App\Models\EmailTemplate;

class EmailTemplateRenderer
{
    public function renderTemplate(EmailTemplate $template, array $context): array
    {
        $replacements = $this->buildReplacements($context);

        return [
            'subject' => $this->replaceTags($template->subject, $replacements),
            'preview_text' => $this->replaceTags($template->preview_text ?? '', $replacements),
            'body_html' => $this->replaceTags($template->body_html, $replacements),
        ];
    }

    public function buildReplacements(array $context): array
    {
        $firstName = $context['first_name']
            ?? data_get($context, 'data.first_name')
            ?? data_get($context, 'data.billing_first_name')
            ?? 'there';
        $lastName = $context['last_name']
            ?? data_get($context, 'data.last_name')
            ?? data_get($context, 'data.billing_last_name')
            ?? '';
        $email = $context['email'] ?? data_get($context, 'data.email') ?? '';
        $phone = $context['phone']
            ?? data_get($context, 'data.phone')
            ?? data_get($context, 'data.billing_phone')
            ?? '';
        $productName = $context['product_name'] ?? data_get($context, 'product.name') ?? 'your order';
        $orderTotalValue = $context['order_total'] ?? data_get($context, 'amount');
        $orderTotal = is_numeric($orderTotalValue)
            ? number_format((float) $orderTotalValue, 2)
            : ($orderTotalValue ?: '0.00');
        $landingPageName = $context['landing_page_name'] ?? data_get($context, 'landing.name') ?? 'our page';
        $unsubscribeUrl = $context['unsubscribe_url'] ?? '#';

        return [
            '{{first_name}}' => $this->safe($firstName),
            '{{last_name}}' => $this->safe($lastName),
            '{{email}}' => $this->safe($email),
            '{{phone}}' => $this->safe($phone),
            '{{product_name}}' => $this->safe($productName),
            '{{order_total}}' => $this->safe($orderTotal),
            '{{landing_page_name}}' => $this->safe($landingPageName),
            '{{unsubscribe_url}}' => $unsubscribeUrl,
        ];
    }

    private function replaceTags(string $value, array $replacements): string
    {
        $result = strtr($value, $replacements);

        // Drop any unsupported leftover tokens gracefully.
        return preg_replace('/\{\{\s*[^}]+\s*\}\}/', '', $result) ?? $result;
    }

    private function safe(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

