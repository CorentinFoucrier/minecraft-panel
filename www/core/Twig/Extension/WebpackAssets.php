<?php

namespace Core\Twig\Extension;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class WebpackAssets extends AbstractExtension
{
    private array $entrypoints;

    public function __construct()
    {
        $this->entrypoints = json_decode(file_get_contents(BASE_PATH . "/www/public/assets/build/entrypoints.json"), true)["entrypoints"];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('webpack_assets', [$this, "webpack_assets"])
        ];
    }

    public function webpack_assets(string $type, string $entrypoint_name)
    {
        if (!key_exists("$entrypoint_name", $this->entrypoints)) {
            throw new \Exception("Webpack entrypoint: \"build/$entrypoint_name.js\" doesn't exist.");
        }

        if ($type === "links") {
            $links = [];
            foreach ($this->entrypoints[$entrypoint_name]["css"] as $value) {
                $links[] = "<link rel=\"stylesheet\" href=\"assets{$value}\" />";
            }
            return implode(PHP_EOL, $links);
        } else if ($type === "scripts") {
            $scripts = [];
            foreach ($this->entrypoints[$entrypoint_name]["js"] as $value) {
                $scripts[] = "<script src=\"assets{$value}\"></script>";
            }
            return implode(PHP_EOL, $scripts);
        } else {
            throw new \Exception("First parameter must be \"links\" or \"scripts\", \"$type\" given.");
        }
    }
}
