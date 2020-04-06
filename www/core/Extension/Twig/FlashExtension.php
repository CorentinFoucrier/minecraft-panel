<?php

namespace Core\Extension\Twig;

use App\App;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Core\Controller\Session\FlashService;

class FlashExtension extends AbstractExtension
{
    private FlashService $flashService;

    public function __construct()
    {
        $this->flashService = App::getInstance()->getFlash();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('flash', [$this, "getFlash"])
        ];
    }

    /**
     * Returns an array of messages associated with the type
     * and delete messages when this methode is called.
     *
     * @return array
     */
    public function getFlash(string $type): array
    {
        return $this->flashService->getMessages($type);
    }
}
