<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class MedicalDataEncryption
{
    /**
     * Encrypt and store a file
     *
     * @param string $fileContent
     * @param string $directory
     * @param string|null $filename
     * @return array ['path' => string, 'hash' => string, 'size' => int]
     * @throws \Exception
     */
    public function encryptAndStoreFile($fileContent, string $directory = 'contracts', ?string $filename = null): array
    {
        try {
            // Generate a secure filename if not provided
            $filename = $filename ?: Str::uuid()->toString() . '.enc';
            $filePath = trim($directory, '/') . '/' . $filename;
            
            // Generate hash of the original content
            $fileHash = hash('sha256', $fileContent);
            
            // Encrypt the file content
            $encryptedContent = Crypt::encrypt($fileContent);
            
            // Store the encrypted content
            Storage::disk('private')->put($filePath, $encryptedContent);
            
            // Get the file size
            $fileSize = Storage::disk('private')->size($filePath);
            
            return [
                'path' => $filePath,
                'hash' => $fileHash,
                'size' => $fileSize,
            ];
        } catch (Exception $e) {
            Log::error('Failed to encrypt and store file', [
                'error' => $e->getMessage(),
                'directory' => $directory,
                'filename' => $filename,
            ]);
            
            throw new \Exception('Failed to encrypt and store file: ' . $e->getMessage());
        }
    }
    
    /**
     * Retrieve and decrypt a file
     *
     * @param string $filePath
     * @return string
     * @throws \Exception
     */
    public function retrieveAndDecryptFile(string $filePath): string
    {
        try {
            if (!Storage::disk('private')->exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
            
            // Get the encrypted content
            $encryptedContent = Storage::disk('private')->get($filePath);
            
            // Decrypt the content
            return Crypt::decrypt($encryptedContent);
        } catch (Exception $e) {
            Log::error('Failed to retrieve and decrypt file', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
            ]);
            
            throw new \Exception('Failed to retrieve and decrypt file: ' . $e->getMessage());
        }
    }
    
    /**
     * Encrypt sensitive data
     *
     * @param mixed $data
     * @return string
     */
    public function encryptData($data): string
    {
        try {
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            
            return Crypt::encryptString((string)$data);
        } catch (Exception $e) {
            Log::error('Failed to encrypt data', [
                'error' => $e->getMessage(),
                'data_type' => gettype($data),
            ]);
            
            throw new \Exception('Failed to encrypt data: ' . $e->getMessage());
        }
    }
    
    /**
     * Decrypt sensitive data
     *
     * @param string $encryptedData
     * @return mixed
     */
    public function decryptData(string $encryptedData)
    {
        try {
            $decrypted = Crypt::decryptString($encryptedData);
            
            // Try to decode JSON if the decrypted data is a JSON string
            $jsonData = json_decode($decrypted, true);
            return json_last_error() === JSON_ERROR_NONE ? $jsonData : $decrypted;
        } catch (Exception $e) {
            Log::error('Failed to decrypt data', [
                'error' => $e->getMessage(),
            ]);
            
            throw new \Exception('Failed to decrypt data: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate a secure encryption key
     * 
     * @return string
     */
    public function generateSecureKey(): string
    {
        return base64_encode(random_bytes(32));
    }
    
    /**
     * Verify file integrity using stored hash
     * 
     * @param string $filePath
     * @param string $storedHash
     * @return bool
     */
    public function verifyFileIntegrity(string $filePath, string $storedHash): bool
    {
        try {
            $fileContent = $this->retrieveAndDecryptFile($filePath);
            $currentHash = hash('sha256', $fileContent);
            
            return hash_equals($storedHash, $currentHash);
        } catch (Exception $e) {
            Log::error('File integrity check failed', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
            ]);
            
            return false;
        }
    }
}
