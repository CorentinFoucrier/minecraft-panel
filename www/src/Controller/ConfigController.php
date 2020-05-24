<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\FormBuilderController;
use Core\Controller\Services\PropertiesService;

class ConfigController extends Controller
{

    public function showForm()
    {
        $this->userOnly();
        $this->hasPermission('config');

        // If server.properties is not null generate and display config form
        if (!empty(SERVER_PROPERTIES)) {
            $form = new FormBuilderController('server.propreties');
            $config = SERVER_PROPERTIES; // Retrieve server.properties content

            // Add checkboxes
            foreach ($config as $key => $value) {
                $value = trim($value);
                $res = ($value === "true" || $value === "false") ? (bool) $value : $value;
                $label = ucfirst(str_replace(['.', '-'], ' ', $key));
                if (is_bool($res)) {
                    if (!in_array($key, ["enable-rcon", "enable-query", "broadcast-rcon-to-ops"])) {
                        $form->addGroup('col-6');
                        $form->addCheckbox("$key", "$value", "$label");
                    }
                }
            }

            $form->addGroup('col-12'); // Add group for separate checkboxes of fields
            foreach ($config as $key => $value) {
                $value = trim($value);
                // cast $value because every $value is a string
                if (is_numeric($value)) {
                    $value = (int) $value;
                } else if ($value === "true" || $value === "false") {
                    $value = (bool) $value;
                }
                $label = ucfirst(str_replace(['.', '-'], ' ', $key)); // Make label "human readable"
                // Add the name of fields that we don't displayed in the exclude array below
                $exclude = [
                    "server-ip", "rcon.password",
                    "enable-rcon", "enable-query",
                    "rcon.port", "query.port",
                    "broadcast-rcon-to-ops"
                ];
                // If is a boolean add $key to the exclude array
                if (is_bool($value)) {
                    $exclude[] = $key;
                }
                if (!in_array($key, $exclude)) {
                    $form->addGroup("col-sm-6");
                }

                // Add select for possible default values that arn't in $config
                switch ($key) {
                    case 'difficulty':
                        $form->addSelect("$key", "$label", [
                            'options' => [
                                'peaceful' => 'peaceful',
                                'easy' => 'easy',
                                'normal' => 'normal',
                                'hard' => 'hard'
                            ],
                            'selected' => $value
                        ]);
                        break;
                    case 'gamemode':
                        $form->addSelect("$key", "$label", [
                            'options' => [
                                'survival' => 'survival',
                                'creative' => 'creative',
                                'adventure' => 'adventure',
                                'spectator' => 'spectator'
                            ],
                            'selected' => $value
                        ]);
                        break;
                    case 'level-type':
                        $form->addSelect("$key", "$label", [
                            'options' => [
                                'default' => 'default',
                                'flat' => 'flat',
                                'largebiomes' => 'largebiomes',
                                'amplified' => 'amplified',
                                'buffet' => 'buffet',
                                'generator-settings' => 'generator-settings'
                            ],
                            'selected' => $value
                        ]);
                        break;
                }

                if (
                    is_string($value)
                    && !empty($value)
                    && !in_array($key, ["difficulty", "gamemode", "level-type"]) // Exclude fixed values in the switch below
                ) { // Treatment for string not boolean, not integer
                    $form->addField("text", "$key", "$label", [
                        'attributes' => [
                            'value' => $value,
                            'placeholder' => $this->lang('config.placeholder.customValue')
                        ]
                    ]);
                } else if (is_numeric($value) && !in_array($key, $exclude)) { // Treatment for integer $value
                    $form->addField("number", "$key", "$label", [
                        'attributes' => [
                            'value' => $value
                        ]
                    ]);
                } else if (is_string($value) && empty($value) && !in_array($key, $exclude)) { // Treatment for empty $value
                    $form->addField("text", "$key", "$label", [
                        'attributes' => [
                            'value' => $value,
                            'placeholder' => $this->lang('config.placeholder.customValue')
                        ]
                    ]);
                }
            }
            $form->addSubmit("send", "Sauvegarder", "btn btn-success btn-sm");
            $form->addToken($this->getCsrfTokenService()->getToken());

            return $this->render("config", [
                'title' => $this->lang('config.title'),
                'form' => $form->getForm()
            ]);
        } else {
            $this->getFlash()->addAlert($this->lang('config.error.properties'));
            return $this->render("noConfig", [
                'title' => $this->lang('config.error.title')
            ]);
        }
    }

    public function send(): void
    {
        if ($_POST['token'] === $_SESSION['token'] && !empty(SERVER_PROPERTIES)) {
            $post = [];
            foreach ($_POST as $key => $value) {
                $post[htmlspecialchars($key)] = htmlspecialchars($value);
            }
            if (!PropertiesService::write($post)) {
                $this->getFlash()->addAlert($this->lang('config.error.properties'));
            }
            $this->getFlash()->addSuccess($this->lang('config.updated'));
            $this->redirect('config');
        }
    }
}
