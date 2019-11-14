<?php

namespace Core\Extension\Twig;

use App\App;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class BeautifyStrExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('beautifyStr', [$this, "beautify"])
        ];
    }

    public function beautify(string $str): string
    {
        $explodedStr = preg_split('/(?=[A-Z])/', $str, -1, PREG_SPLIT_NO_EMPTY);
        return ucfirst(strtolower(join(" ", $explodedStr)));
    }
}
