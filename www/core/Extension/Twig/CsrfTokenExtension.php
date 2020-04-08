<?php

namespace Core\Extension\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Core\Controller\Services\CsrfTokenService;

class CsrfTokenExtension extends AbstractExtension
{
    private CsrfTokenService $service;

    public function __construct(CsrfTokenService $service)
    {
        $this->service = $service;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_token', [$this->service, "getToken"])
        ];
    }
}
