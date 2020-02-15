<?php

namespace Core\Controller\Session;

interface SessionInterface
{
    /**
     * Get a session info
     */
    public function get(string $key, $default = null);

    /**
     * Set a session info
     */
    public function set(string $key, $value): void;

    /**
     * Delete a session info
     */
    public function delete(string $key): void;
}
