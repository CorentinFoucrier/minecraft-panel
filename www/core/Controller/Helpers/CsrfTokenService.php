<?php

namespace Core\Controller\Helpers;

class CsrfTokenService
{
    /**
     * Generating a CSRF Token
     *
     * @see https://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
     * @param string|null $lock_to_route
     * @return string
     */
    public function getToken(?string $lock_to_route = null): string
    {
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }
        if (empty($_SESSION['token2'])) {
            $_SESSION['token2'] = random_bytes(32);
        }
        if (!empty($lock_to_route)) {
            return hash_hmac('sha256', $lock_to_route, $_SESSION['token2']);
        }
        return $_SESSION['token'];
    }
}
