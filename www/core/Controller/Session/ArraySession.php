<?php

namespace Core\Controller\Session;

class ArraySession implements SessionInterface
{
    private array $session = [];

    /**
     * Get a session info
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->session)) {
            return $this->session[$key];
        }
        return $default;
    }

    /**
     * Set a session info
     */
    public function set(string $key, $value): void
    {
        $this->session[$key][] = $value;
    }


    /**
     * Delete a session info
     */
    public function delete(string $key): void
    {
        unset($this->session[$key]);
    }
}
