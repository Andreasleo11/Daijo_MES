<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BaseSapService
{
    protected $baseUrl = 'http://192.168.6.149:9001';
    protected $authUrl = 'http://192.168.6.149:9001/auth/token';
    protected $token;
    
    public function __construct()
    {
        // Jangan authenticate di constructor - lazy load aja
        $this->token = null;
    }
    
    /**
     * Get token - lazy load when needed
     */
    protected function getToken()
    {
        if ($this->token) {
            return $this->token;
        }
        
        // Try cache dulu
        $cachedToken = Cache::get('sap_token');
        if ($cachedToken) {
            $this->token = $cachedToken;
            return $this->token;
        }
        
        // Kalau cache kosong, authenticate
        $this->token = $this->authenticate();
        Cache::put('sap_token', $this->token, now()->addMinutes(50));
        
        return $this->token;
    }
    
    /**
     * Authenticate to SAP API and get the token
     */
    protected function authenticate()
    {
        try {
            $response = Http::withHeaders([
                    'Host' => 'localhost',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(10)
                ->post($this->authUrl, [
                    'CompanyDB' => env('SAP_COMPANY_DB'),
                    'Username' => env('SAP_USERNAME'),
                    'Password' => env('SAP_PASSWORD'),
                ]);
                
            if ($response->successful()) {
                Log::info('SAP Authentication successful');
                return $response->json()['access_token'];
            }
            
            Log::error('SAP Auth failed: ' . $response->status() . ' - ' . $response->body());
            throw new \Exception('Failed to authenticate to SAP: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('SAP Authentication error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send a GET request to SAP API
     */
    protected function get($endpoint, $params = [])
    {
        try {
            $token = $this->getToken();
            
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Host' => 'localhost',
                ])
                ->timeout(30)
                ->get($this->baseUrl . $endpoint, $params);
                
            if ($response->successful()) {
                return $response->json();
            }
            
            // 401 = token expired
            if ($response->status() === 401) {
                Log::warning('SAP token expired, refreshing...');
                Cache::forget('sap_token');
                $this->token = null;
                
                // Retry once with fresh token
                $token = $this->getToken();
                $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                        'Host' => 'localhost',
                    ])
                    ->timeout(30)
                    ->get($this->baseUrl . $endpoint, $params);
                    
                return $response->json();
            }
            
            throw new \Exception('SAP API Error: ' . $response->status());
            
        } catch (\Exception $e) {
            Log::error('SAP GET request error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send a POST request to SAP API
     */
    protected function post($endpoint, $payload = [])
    {
        try {
            $token = $this->getToken();
            
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',  // ← tambah ini
                    'Host'          => 'localhost',
                ])
                ->timeout(30)
                ->post($this->baseUrl . $endpoint, $payload);
                
            if ($response->successful()) {
                return $response;
            }
            
            if ($response->status() === 401) {
                Log::warning('SAP token expired during POST, refreshing...');
                Cache::forget('sap_token');
                $this->token = null;
                
                $token = $this->getToken();
                $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,  // ← fix typo ini
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',  // ← tambah ini
                        'Host'          => 'localhost',
                    ])
                    ->timeout(30)
                    ->post($this->baseUrl . $endpoint, $payload);
                    
                return $response;
            }
            
            throw new \Exception('SAP API Error: ' . $response->status());
            
        } catch (\Exception $e) {
            Log::error('SAP POST request error: ' . $e->getMessage());
            throw $e;
        }
    }
        
    public function testGet($endpoint, $params = [])
    {
        return $this->get($endpoint, $params);
    }

    public function testPost()
    {
        return $this->post($this->endpoint, []);
    }
}