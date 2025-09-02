<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class BaseSapService
{
    protected $baseUrl = 'http://192.168.6.149:9001';
    protected $authUrl = 'http://192.168.6.149:9001/auth/token';
    protected $token;
    
    public function __construct()
    {
        // Force clear session token untuk bypass cache issue
        Session::forget('sap_token');
        
        // Always fresh token
        $this->token = $this->authenticate();
        Session::put('sap_token', $this->token);
    }
    
    /**
     * Authenticate to SAP API and get the token
     */
    protected function authenticate()
    {
        $response = Http::withHeaders([
                'Host' => 'localhost',
                'Content-Type' => 'application/json',
            ])
            ->post($this->authUrl, [
                'CompanyDB' => 'LIVE_DATABASE',
                'Username' => 'it02',
                'Password' => '123it',
            ]);
            
        if ($response->successful()) {
            return $response->json()['access_token'];
        }
        
        throw new \Exception('Failed to authenticate to SAP: ' . $response->body());
    }
    
    /**
     * Fresh token setiap kali dipanggil
     */
    private function getFreshToken()
    {
        // Clear session dan get token baru
        Session::forget('sap_token');
        $this->token = $this->authenticate();
        Session::put('sap_token', $this->token);
        return $this->token;
    }
    
    /**
     * Send a GET request to SAP API
     */
    protected function get($endpoint, $params = [])
    {
        // ALWAYS use fresh token - fuck cache
        $token = $this->getFreshToken();
        
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Host' => 'localhost',
            ])
            ->get($this->baseUrl . $endpoint, $params);
            
        // Kalau masih 401 setelah fresh token, something is wrong
        if ($response->status() === 401) {
            // Try one more time with brand new token
            $token = $this->getFreshToken();
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Host' => 'localhost',
                ])
                ->get($this->baseUrl . $endpoint, $params);
        }
        
        return $response->json();
    }
    
    protected function post($endpoint, $payload = [])
    {
        // ALWAYS use fresh token - fuck cache
        $token = $this->getFreshToken();
        
        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Host' => 'localhost',
            ])
            ->post($this->baseUrl . $endpoint, $payload);
            
        // Kalau masih 401 setelah fresh token, something is wrong
        if ($response->status() === 401) {
            // Try one more time with brand new token
            $token = $this->getFreshToken();
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Host' => 'localhost',
                ])
                ->post($this->baseUrl . $endpoint, $payload);
        }
        
        return $response;
    }
    
    public function getToken()
    {
        return $this->token;
    }
    
    public function testGet($endpoint, $params = [])
    {
        return $this->get($endpoint, $params);
    }
}