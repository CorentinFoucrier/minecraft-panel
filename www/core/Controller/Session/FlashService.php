<?php

namespace Core\Controller\Session;

class FlashService
{
    private SessionInterface $session;

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

    public function addWarning(string $message): void
    {
        $this->session->set("warning", $message);
    }

    public function getMessages(string $type): array
    {
        $message = $this->session->get($type, []);
        $this->session->delete($type);
        return $message;
    }

    public function hasMessages(string $type): bool
    {
        return ($this->session->get($type, false)) ? true : false;
    }
}
