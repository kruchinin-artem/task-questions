<?php

namespace App\Service;

use Exception;

class WebhookProcessor
{
    const CACHE_PREFIX = 'webhook_';
    const CACHE_TTL = 3600;

    /**
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function processWebhook(array $data): void
    {
        $type = $data['type'] ?? null;
        if ($type !== 'message') {
            throw new Exception('Unsupported type');
        }

        $message = $data['text'] ?? null;
        if ($message === null) {
            throw new Exception('Text cannot be empty');
        }

        $externalId = (string)($data['id'] ?? '');
        if (empty($externalId)) {
            throw new Exception('Missing external_id in webhook data');
        }

        // Check for duplicate processing
        if ($this->isWebhookProcessed($externalId)) {
            return;
        }

        $fields = $data['fields'] ?? [];
        $this->_storeDeal(
            'Deal from webhook',
            $message,
            json_encode($fields),
            $externalId
        );

        $this->markAsProcessed($externalId);
    }

    /**
     * @param string $externalId Unique webhook ID
     * @return bool
     */
    private function isWebhookProcessed(string $externalId): bool
    {
        $cacheKey = self::CACHE_PREFIX . $externalId;
        
        if ($this->_getDataFromCache($cacheKey) !== null) {
            return true;
        }

        return $this->_getDealByExternalId($externalId) !== null;
    }

    /**
     * @param string $externalId Unique webhook ID
     */
    private function markAsProcessed(string $externalId): void
    {
        $cacheKey = self::CACHE_PREFIX . $externalId;
        $this->_setDataToCache($cacheKey, ['status' => 'processed'], self::CACHE_TTL);
    }

    
    private function _storeDeal(
        string $title, 
        string $text, 
        string $fields, 
        ?string $externalId = null
    ): void {
        // Stores deal in database
    }

    private function _setDataToCache(string $key, array $data, int $expires): void {
        // Saves array data to cache
    }

    private function _getDealByExternalId(string $externalId): ?array {
        // Returns deal array if exists, null otherwise
        return null;
    }
    
    private function _getDataFromCache(string $key): ?string {
        // Returns cached string data
        return null;
    }
}
