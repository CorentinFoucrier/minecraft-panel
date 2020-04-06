<?php

namespace Core\Controller\Session;

/**
 * Manage $_SESSION informations
 */
class PhpSession implements SessionInterface
{
    /**
     * Get $_SESSION value of $key
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Set $_SESSION $key and the $value in a sub array
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key][] = $value;
    }

    /**
     * Delete session $key
     */
    public function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
