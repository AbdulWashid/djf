<?php
// File path: app/Helpers/helpers.php

if (!function_exists('rememberIfEnabled')) {
    /**
     * Cache store and get function
     */
    function rememberIfEnabled(string $key, $ttl, Closure $callback)
    {
        return config('cache.enable') ? Cache::remember($key, $ttl, $callback) : $callback();
    }
}
