<?php
namespace Core\Controller\Session;
/**
 * Manage $_SESSION informations
 */
class PhpSession implements SessionInterface, \ArrayAccess
{
    /**
     * Get $_SESSION value of $key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->ensureStarted();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;
    }
    /**
     * Set $_SESSION $key and the $value in a sub array
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key][] = $value;
    }

    /**
     * Delete session $key
     * 
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Start the session if isn't started yet.
     *
     * @return void
     */
    private function ensureStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function offsetExists($key): bool
    {
        $this->ensureStarted();
        return array_key_exists($key, $_SESSION);
    }
    
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }
}
