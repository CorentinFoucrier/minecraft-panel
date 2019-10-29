<?php

namespace Core\Extension\Twig;

use Twig\Extension\AbstractExtension;
use Core\Controller\URLController;
use Twig\TwigFunction;

class URIExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uri', [$this, "getUri"])
        ];
    }

    public function getUri(string $name, array $params = []): string
    {
        return  URLController::getUri($name, $params);
    }
}
