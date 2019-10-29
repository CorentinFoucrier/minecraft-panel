<?php

namespace Core\Controller\Session;

class FlashService
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function addSuccess(string $message): void
    {
        $this->session->set("success", $message);
    }

    public function addAlert(string $message): void
    {
        $this->session->set("alert", $message);
    }

    public function getMessages(string $key): array
    {
        $message =  $this->session->get($key, []);
        $this->session->delete($key);
        return $message;
    }

    public function hasMessages(string $key): bool
    {
        if ($this->session->get($key, false)) {
            return true;
        }
        return false;
    }
}
