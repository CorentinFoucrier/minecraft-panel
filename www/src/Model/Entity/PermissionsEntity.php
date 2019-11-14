<?php
namespace App\Model\Entity;

use Core\Model\Entity;

class PermissionsEntity extends Entity
{
    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $user_id
     */
    private $user_id;

    /**
     * @var int $start_and_stop
     */
    private $start_and_stop;

    /**
     * @var int $change_version
     */
    private $change_version;

    /**
     * @var int $send_command
     */
    private $send_command;

    /**
     * @var int $plugins
     */
    private $plugins;

    /**
     * @var int $config
     */
    private $config;

    /**
     * @var int $world_management
     */
    private $worlds_management;

    /**
     * @var int $players_management
     */
    private $players_management;

    /**
     * @var int $scheduled_tasks
     */
    private $scheduled_tasks;

    /**
     * @var int $file_export
     */
    private $file_export;

    /**
     * @var int $co_admin
     */
    private $co_admin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId($user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getStartAndStop(): ?int
    {
        return $this->start_and_stop;
    }

    public function setStartAndStop($start_and_stop): self
    {
        $this->start_and_stop = $start_and_stop;
        return $this;
    }

    public function getChangeVersion(): ?int
    {
        return $this->change_version;
    }

    public function setChangeVersion($change_version): self
    {
        $this->change_version = $change_version;
        return $this;
    }

    public function getSendCommand(): ?int
    {
        return $this->send_command;
    }

    public function setSendCommand($send_command): self
    {
        $this->send_command = $send_command;
        return $this;
    }

    public function getPlugins(): ?int
    {
        return $this->plugins;
    }

    public function setPlugins($plugins): self
    {
        $this->plugins = $plugins;
        return $this;
    }

    public function getConfig(): ?int
    {
        return $this->config;
    }

    public function setConfig($config): self
    {
        $this->config = $config;
        return $this;
    }

    public function getWorldsManagement(): ?int
    {
        return $this->worlds_management;
    }

    public function setWorldsManagement($worlds_management): self
    {
        $this->worlds_management = $worlds_management;
        return $this;
    }

    public function getPlayersManagement(): ?int
    {
        return $this->players_management;
    }

    public function setPlayersManagement($players_management): self
    {
        $this->players_management = $players_management;
        return $this;
    }

    public function getScheduledTasks(): ?int
    {
        return $this->scheduled_tasks;
    }

    public function setScheduledTasks($scheduled_tasks): self
    {
        $this->scheduled_tasks = $scheduled_tasks;
        return $this;
    }

    public function getFileExport(): ?int
    {
        return $this->file_export;
    }

    public function setFileExport($file_export): self
    {
        $this->file_export = $file_export;
        return $this;
    }

    public function getCoAdmin(): ?int
    {
        return $this->co_admin;
    }

    public function setCoAdmin($co_admin): self
    {
        $this->co_admin = $co_admin;
        return $this;
    }
}
