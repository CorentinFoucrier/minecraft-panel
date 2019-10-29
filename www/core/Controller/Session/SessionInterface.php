<?php
namespace Core\Controller\Session;

interface SessionInterface
{
    /**
     * recupère une  info en session
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * mettre une  info en session
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void;


    /**
     * supprime une  info en session
     * @param string $key
     */
    public function delete(string $key): void;
}
