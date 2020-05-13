<?php

namespace Core\Twig\Filter;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class HumanizeFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('humanize', [$this, "humanize"])
        ];
    }

    public function humanize(string $str): string
    {
        $subCategory = preg_split('/([a-z]+__)/', $str, -1, PREG_SPLIT_NO_EMPTY);
        if ($subCategory) {
            $str = join('', $subCategory);
        }
        return ucfirst(join(" ", explode('_', $str)));
    }
}
