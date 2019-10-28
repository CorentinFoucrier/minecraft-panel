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
        if (!empty($_POST) && !is_null(SERVER_PROPERTIES)) {
            if (!$this->writeServerProperties($_POST)) {
                $this->getFlash()->addAlert("Le fichier serveur.properties n'a pas été trouvé.\n
                Démarrer le serveur pour générer le fichier.");
            }
            $this->redirect($this->generateUrl('config'));
        }

        /* If server.properties is not null display config form */
        if (!is_null(SERVER_PROPERTIES)) {
            $form = new FormBuilderController('server.propreties');
            $config = SERVER_PROPERTIES; // Retrieve server.properties content
            foreach ($config as $key => $value) {
                $label = ucfirst(str_replace(['.', '-'], ' ', $key));
                /* add the name of fields that are not displayed in the array below */
                if (!in_array($key, [
                    "server-ip", "rcon.password",
                    "enable-rcon", "enable-query",
                    "rcon.port", "query.port",
                    "broadcast-rcon-to-ops"])
                    ) {
                    $form->addGroup("col-sm-6");
                }
                /** 
                 * If 'true' is matched, that's a boolean then when 'true'
                 * add select with default selected value as 'true', same with 'false'
                 */
                if ((preg_match('/(true|false)++/', $value)) === 1) {
                    if (!in_array($key, ["enable-rcon", "enable-query", "broadcast-rcon-to-ops"])) {
                        $form->addSelect("$key", "$label",[
                            'options' => ['true'=>'true','false'=>'false'],
                            'selected' => $value
                        ]);
                    }
                }
                //Add select for possible default values that arn't in $config
                switch ($key) {
                    case 'difficulty':
                        $form->addSelect("$key", "$label",[
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
                        $form->addSelect("$key", "$label",[
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
                        $form->addSelect("$key", "$label",[
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
                /* Treatment for string but not boolean and custom select fields above */
                if ((preg_match('/^(?!true$|false$)[a-zA-Z]++/', $value)) === 1) {
                    if (!in_array($key, ["difficulty", "gamemode", "level-type"])) {
                        $form->addField("text", "$key", "$label", [
                            'attributes' => [
                                'value' => $value,
                                'placeholder' => 'Custom value'
                            ]
                        ]);
                    }
                }
                /* Treatment for integer $value */
                if (preg_match('/[0-9]/', $value)) {
                    if (!in_array($key, ["rcon.port", "query.port"])) {
                        $form->addField("number", "$key", "$label", [
                            'attributes' => [
                                'value' => $value
                            ]
                        ]);
                    }
                }
                /* Treatment for empty $value */
                if ($value === "") {
                    if (!in_array($key, ["server-ip", "rcon.password"])) {
                        $form->addField("text", "$key", "$label", [
                            'attributes' => [
                                'value' => $value,
                                'placeholder' => 'Custom value'
                            ]
                        ]);
                    }
                }
            }
            $form->addSubmit("send", "Envoyer", "btn btn-success btn-sm");

            return $this->render("config", [
                'title' => "Configurations",
                'form' => $form->getForm()
            ]);
        } else {
            $this->getFlash()->addAlert("Le fichier serveur.properties n'a pas été trouvé.\n
            Démarrer le serveur pour générer le fichier.");
            return $this->render("noConfig", [
                'title' => "Configuration non trouvé!"
            ]);
        }
    }

    private function writeServerProperties(array $post): bool
    {
        $config = SERVER_PROPERTIES;
        /* this foreach will integrate user constants values */
        foreach ($config as $key => $value) {
            /* Change the actual $key to constant eg. my-key => MY_KEY */
            $constStr = strtoupper(str_replace(['.', '-'], '_', $key));
            if (defined($constStr)) {
                /* If is defined, put the value of the constant in $const */
                $const = constant($constStr);
            }

            $constArray = get_defined_constants(true);//Get categorized array of defined constants

            if (array_key_exists($constStr, $constArray['user'])) {
                /* If the constant is in user defined constant array replace default $value to $const */
                $config[$key] = $const;
            } else {
                $config[$key] = $value;
            }
        }

        /* Convert $config array to original properties file with user entries */
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
