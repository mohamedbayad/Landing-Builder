<?php

namespace App\Services;

use App\Models\CustomDomain;

class DomainVerificationService
{
    public function verify(CustomDomain $domain): bool
    {
        // Check CNAME record
        $cnameRecords = dns_get_record($domain->domain, DNS_CNAME);
        $mainDomain = config('app.main_domain');
        $cnameValid = collect($cnameRecords)->contains(function($record) use ($mainDomain) {
            return str_contains($record['target'] ?? '', $mainDomain);
        });
        
        // Check TXT verification record
        $txtRecords = dns_get_record('_builder-verify.' . $domain->domain, DNS_TXT);
        $expectedTxt = 'builder-verify=' . $domain->verification_token;
        $txtValid = collect($txtRecords)->contains(function($record) use ($expectedTxt) {
            return ($record['txt'] ?? '') === $expectedTxt;
        });
        
        if ($cnameValid && $txtValid) {
            $domain->update([
                'status' => 'active',
                'verified_at' => now(),
                'error_message' => null,
            ]);
            return true;
        }
        
        $domain->update([
            'status' => 'pending',
            'error_message' => ! $cnameValid 
                ? 'CNAME record not found. DNS may take up to 48 hours to propagate.' 
                : 'TXT verification record not found.',
        ]);
        
        return false;
    }
}
