<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Contract;
use Exception;

class WordPressIntegration
{
    private $client;
    private $baseUrl;
    private $apiKey;
    private $cacheTtl = 3600; // 1 hour
    
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 15,
            'connect_timeout' => 5,
            'http_errors' => false,
        ]);
        
        $this->baseUrl = rtrim(config('services.wordpress.url', ''), '/');
        $this->apiKey = config('services.wordpress.api_key');
    }
    
    /**
     * Get all contract templates from WordPress
     *
     * @return array
     * @throws \Exception
     */
    public function getContractTemplates(): array
    {
        $cacheKey = 'wordpress_contract_templates';
        
        try {
            return Cache::remember($cacheKey, $this->cacheTtl, function () {
                $response = $this->client->get($this->baseUrl . '/wp-json/wp/v2/contract-templates', [
                    'headers' => $this->getAuthHeaders(),
                    'query' => [
                        'per_page' => 100,
                        'status' => 'publish',
                    ]
                ]);
                
                return $this->handleResponse($response);
            });
        } catch (Exception $e) {
            Log::error('Failed to fetch WordPress contract templates', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl . '/wp-json/wp/v2/contract-templates'
            ]);
            
            throw new \Exception('Failed to fetch contract templates: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a new WordPress post for a contract
     *
     * @param Contract $contract
     * @return array
     * @throws \Exception
     */
    public function createContractPost(Contract $contract): array
    {
        try {
            $postData = [
                'title' => $contract->title,
                'content' => $this->formatContractContent($contract),
                'status' => 'draft', // Start as draft for review
                'meta' => [
                    'contract_id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'medical_center_id' => $contract->medical_center_id,
                    'medical_center' => $contract->medicalCenter->name,
                    'contract_value' => $contract->contract_value,
                    'start_date' => $contract->start_date->toDateString(),
                    'end_date' => $contract->end_date->toDateString(),
                    'vendor_name' => $contract->vendor_name,
                ]
            ];
            
            $response = $this->client->post($this->baseUrl . '/wp-json/wp/v2/posts', [
                'headers' => $this->getAuthHeaders('application/json'),
                'json' => $postData
            ]);
            
            $result = $this->handleResponse($response);
            
            // Clear the cache for contract templates
            Cache::forget('wordpress_contract_templates');
            
            return $result;
        } catch (Exception $e) {
            Log::error('Failed to create WordPress post for contract', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Failed to create WordPress post: ' . $e->getMessage());
        }
    }
    
    /**
     * Update an existing WordPress post for a contract
     *
     * @param Contract $contract
     * @param string $wpPostId
     * @return array
     * @throws \Exception
     */
    public function updateContractPost(Contract $contract, string $wpPostId): array
    {
        try {
            $postData = [
                'title' => $contract->title,
                'content' => $this->formatContractContent($contract),
                'meta' => [
                    'contract_number' => $contract->contract_number,
                    'medical_center' => $contract->medicalCenter->name,
                    'contract_value' => $contract->contract_value,
                    'start_date' => $contract->start_date->toDateString(),
                    'end_date' => $contract->end_date->toDateString(),
                    'vendor_name' => $contract->vendor_name,
                ]
            ];
            
            $response = $this->client->post(
                $this->baseUrl . "/wp-json/wp/v2/posts/{$wpPostId}",
                [
                    'headers' => $this->getAuthHeaders('application/json'),
                    'json' => $postData
                ]
            );
            
            return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error('Failed to update WordPress post for contract', [
                'contract_id' => $contract->id,
                'wp_post_id' => $wpPostId,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to update WordPress post: ' . $e->getMessage());
        }
    }
    
    /**
     * Format contract content for WordPress
     *
     * @param Contract $contract
     * @return string
     */
    private function formatContractContent(Contract $contract): string
    {
        $content = [
            '<div class="contract-details">',
            '<h2>Contract Details</h2>',
            '<div class="contract-meta">',
            '<p><strong>Contract Number:</strong> ' . e($contract->contract_number) . '</p>',
            '<p><strong>Type:</strong> ' . ucfirst(e($contract->contract_type)) . '</p>',
            '<p><strong>Vendor:</strong> ' . e($contract->vendor_name) . '</p>',
            '<p><strong>Value:</strong> $' . number_format($contract->contract_value, 2) . '</p>',
            '<p><strong>Duration:</strong> ' . e($contract->start_date->format('F j, Y')) . ' to ' . e($contract->end_date->format('F j, Y')) . '</p>',
            '<p><strong>Status:</strong> ' . ucfirst(e($contract->status)) . '</p>',
            '</div>',
            ];
            
        if (!empty($contract->description)) {
            $content[] = '<div class="contract-description">';
            $content[] = '<h3>Description</h3>';
            $content[] = '<p>' . nl2br(e($contract->description)) . '</p>';
            $content[] = '</div>';
        }
        
        $content[] = '</div>';
        
        return implode("\n", $content);
    }
    
    /**
     * Get authentication headers
     *
     * @param string $contentType
     * @return array
     */
    private function getAuthHeaders(string $contentType = 'application/json'): array
    {
        $headers = [
            'Accept' => $contentType,
            'User-Agent' => 'Medical Contract System/1.0',
        ];
        
        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        
        if ($contentType) {
            $headers['Content-Type'] = $contentType;
        }
        
        return $headers;
    }
    
    /**
     * Handle API response
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     * @throws \Exception
     */
    private function handleResponse($response): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        
        if ($statusCode >= 400) {
            $error = json_decode($body, true) ?? ['message' => 'Unknown error'];
            throw new \Exception(
                $error['message'] ?? 'WordPress API request failed',
                $statusCode
            );
        }
        
        return json_decode($body, true) ?? [];
    }
}
