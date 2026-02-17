<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('LICENSING_SERVER_URL', 'http://127.0.0.1:8001/api');
    }

    public function activate($key)
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/activate", [
                'key' => $key,
                'domain' => request()->getHost(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'token' => $data['token'],
                    'valid_until' => $data['valid_until'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Activation failed',
            ];
        } catch (\Exception $e) {
            Log::error('License Activation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Could not connect to licensing server. Please try again later.',
            ];
        }
    }

    public function getTemplates()
    {
        $token = $this->getLicenseToken();

        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)->timeout(5)->get("{$this->baseUrl}/templates");

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                // Cache templates for 1 hour to reduce API calls and provide offline support
                Cache::put('remote_templates', $data, now()->addHour());
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Template Fetch Error: ' . $e->getMessage());
            
            // Return cached templates if available (Offline Mode)
            if (Cache::has('remote_templates')) {
                return Cache::get('remote_templates');
            }
        }

        return [];
    }

    protected function getLicenseToken()
    {
        // Retrieve token from storage. 
        // For MVP, checking Cache or Settings. 
        // Let's assume the controller saves it to Settings model 'license_data' column (json) or specific columns.
        // Or we can use Cache::get('license_token');
        return Cache::get('license_token');
    }
}
