<?php
namespace UnitedSearch\Service;

interface CacheInterface
{
    /**
     * Get an item from the cache
     * 
     * @param string $key Cache key
     * @return mixed|null Cached value or null if not found
     */
    public function getItem($key);

    /**
     * Set an item in the cache
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @return bool Success status
     */
    public function setItem($key, $value);

    /**
     * Check if an item exists in the cache
     * 
     * @param string $key Cache key
     * @return bool
     */
    public function hasItem($key);

    /**
     * Remove an item from the cache
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public function removeItem($key);
}