<?php
namespace Core\Controller\Session;

class PhpSession implements SessionInterface, \ArrayAccess
{
    /**
     * recupère une  info en session
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
     * mettre une  info en session
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key][] = $value;
    }

    /**
     * supprime une  info en session
     * @param string $key
     */
    public function delete(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }


    private function ensureStarted():void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function offsetExists($key) : bool
    {
        $this->ensureStarted();
        return array_key_exists($key, $_SESSION);
    }
    
    public function offsetGet($offset) : mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) : void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) : void
    {
        $this->delete($offset);
    }
}
