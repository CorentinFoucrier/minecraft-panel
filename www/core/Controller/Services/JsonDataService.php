<?php

namespace Core\Controller\Services;

/**
 * This class can be used to obtain a JSON representation  
 * of a PHP array and sent throught an AJAX request
 */
class JsonDataService
{
    private array $data = [];

    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
        $this->data['state'] = $state;
    }

    /**
     * Add a custom key and value
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function add(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function addToast(string $message, string $title = ""): self
    {
        $this->data[$this->state] = [
            'message' => $message,
            'title' => $title
        ];
        return $this;
    }

    /**
     * echo a JSON representation of a PHP array
     *
     * @return void
     */
    public function echo(): void
    {
        echo ($json = json_encode($this->data, JSON_UNESCAPED_UNICODE))
            ? $json
            // In case of "json_encode" error return a raw json server error.
            : "{'state': 'error', 'error': {'message': 'Internal server error', 'title': '500'} }";
    }
}
