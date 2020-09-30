<?php

namespace Core\Controller\Services;

class GitHubAPI
{
    /**
     * @param string $filename
     * @return string JSON string
     */
    public static function get(string $filename): string
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.github.com/gists/fd89fb685ff704f246084100b0ae8982",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Accept: application/vnd.github.v3+json",
                "User-Agent: PHP/" . phpversion()
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true)["files"]["{$filename}.json"]["content"];
    }
}
