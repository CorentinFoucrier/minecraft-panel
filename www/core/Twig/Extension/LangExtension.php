<?php

namespace Core\Twig\Extension;

use App\App;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class LangExtension extends AbstractExtension
{
    private App $app;

    public function __construct()
    {
        $this->app = App::getInstance();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('lang', [$this->app, "getLang"])
        ];
    }
}
