<?php

namespace Core\Extension\Twig;

use Twig\Extension\AbstractExtension;
use Core\Controller\Session\FlashService;
use Twig\TwigFunction;

class FlashExtension extends AbstractExtension
{
    /**
     * Stockage de la session ;) 
     *
     * @var FlashService 
     */
    private $flashService;

    public function __construct()
    {
        $this->flashService = \App\App::getInstance()->flash();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('flash', [$this, "getFlash"])
        ];
    }

    public function getFlash(string $type): array
    {
        return $this->flashService->getMessages($type);
    }
}
