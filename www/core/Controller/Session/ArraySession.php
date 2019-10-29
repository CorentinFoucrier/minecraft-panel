<?php
namespace Core\Controller\Session;

class ArraySession implements SessionInterface
{
    private $session = [];

    /**
     * recupère une  info en session
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->session)) {
            return $this->session[$key];
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
        $this->session[$key][] = $value;
    }


    /**
     * supprime une  info en session
     * @param string $key
     */
    public function delete(string $key): void
    {
        unset($this->session[$key]);
    }
}
