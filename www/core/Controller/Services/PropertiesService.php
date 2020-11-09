<?php

namespace Core\Controller\Services;

class PropertiesService
{
    public static string $propertiesPath = BASE_PATH . 'minecraft_server/server.properties';

    public static function get(): ?array
    {
        if (!is_file(self::$propertiesPath)) return null;
        if (!$content = file_get_contents(self::$propertiesPath)) return null;

        // Search and split in 2 groups where is "=": (key)=(value)
        $regex = '/(.+)=(.*)/m';
        // Result of regex in an array of $matches
        preg_match_all($regex, $content, $matches, PREG_SET_ORDER, 0);
        // Generate $config assoc array
        for ($i = 0; $i < count($matches); $i++) {
            $config[$matches[$i][1]] = htmlspecialchars($matches[$i][2], ENT_QUOTES);
        }
        ksort($config); // Because... why not? ^^
        return $config;
    }

    public static function write(array $post): bool
    {
        $config = self::get();
        // this foreach will integrate user constants values
        foreach ($config as $key => $value) {
            // Change the actual $key to constant eg. my-key => MY_KEY
            $constStr = strtoupper(str_replace(['.', '-'], '_', $key));
            if (defined($constStr)) {
                // If is defined, put the value of the constant in $const
                $const = constant($constStr);
            }

            $constArray = get_defined_constants(true); //Get categorized array of defined constants

            if (array_key_exists($constStr, $constArray['user'])) {
                // If the constant is in user defined constant array replace default $value to $const
                $config[$key] = $const;
            } else {
                $config[$key] = $value;
            }
        }

        // Convert $config array to original properties file with user entries
        $serverProperties = "# I'm an auto generated file ;)\n";
        foreach ($config as $key => $value) {
            if (key_exists($key, $post)) {
                $serverProperties .= "$key=$post[$key]\n";
            } else {
                $serverProperties .= "$key=$value\n";
            }
        }

        // Create the server.properties file
        if (is_int(file_put_contents(self::$propertiesPath, $serverProperties))) {
            return true;
        } else {
            return false;
        }
    }
}
