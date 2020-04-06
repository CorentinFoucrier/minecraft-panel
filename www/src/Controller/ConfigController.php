<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\FormBuilderController;
use Core\Controller\Helpers\ServerPropertiesController;

class ConfigController extends Controller
{

    public function showForm()
    {
        $this->userOnly();
        $this->hasPermission('config');
        // When $_POST ->writeServerProperties
        if (htmlspecialchars($_POST['token']) === $_SESSION['token'] && !empty(SERVER_PROPERTIES)) {
            $post = [];
            foreach ($_POST as $key => $value) {
                $post[htmlspecialchars($key)] = htmlspecialchars($value);
            }
            if (!$this->writeServerProperties($post)) {
                $this->getFlash()->addAlert("Le fichier serveur.properties n'a pas été trouvé.\n
                Démarrer le serveur pour générer le fichier.");
            }
            $this->getFlash()->addSuccess("Configuration mise à jour !");
            $this->redirect('config');
        }

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
                            'placeholder' => 'Custom value'
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
                            'placeholder' => 'Custom value'
                        ]
                    ]);
                }
            }
            $form->addSubmit("send", "Sauvegarder", "btn btn-success btn-sm");
            $form->addToken($this->getCsrfTokenService()->getToken());

            return $this->render("config", [
                'title' => "Configurations",
                'form' => $form->getForm()
            ]);
        } else {
            $this->getFlash()->addAlert(
                "Le fichier serveur.properties n'a pas été trouvé.\n
                Démarrer le serveur pour générer le fichier."
            );
            return $this->render("noConfig", [
                'title' => "Configuration non trouvé!"
            ]);
        }
    }

    private function writeServerProperties(array $post): bool
    {
        $config = SERVER_PROPERTIES;
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

        if (is_int(file_put_contents(ServerPropertiesController::$filePath, $serverProperties))) {
            return true;
        } else {
            return false;
        }
    }
}
