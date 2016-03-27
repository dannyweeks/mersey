<?php

namespace Weeks\Mersey;

use Weeks\Mersey\Services\Ping;
use Weeks\Mersey\Traits\PassThruTrait;

class Server
{
    use PassThruTrait;
    /**
     * @var Ping
     */
    protected $pingService;
    private $name;
    private $username;
    private $hostname;
    /**
     * @var string
     */
    private $displayName;
    /**
     * @var array
     */
    private $projects;
    /**
     * @var string
     */
    private $sshKey;
    /**
     * @var integer
     */
    private $sshPort;
    private $mersey;

    public function __construct(Mersey $mersey, $config)
    {
        $this->mersey = $mersey;
        $this->name = $config->name;
        $this->username = $config->username;
        $this->hostname = $config->hostname;
        $this->displayName = $config->displayName;
        $this->sshPort = isset($config->port) ? $config->port : 22;
        $this->sshKey = isset($config->sshKey) ? $config->sshKey : '~/.ssh/id_rsa';

        $this->projects = collect();
        if (isset($config->projects)) {
            foreach ($config->projects as $projectConfig) {
                $this->projects->push(new Project($mersey, $projectConfig));
            }
        }
    }

    /**
     * Get the full SSH command tailored to this servers config.
     *
     * @param string $remoteCommand
     * @return bool|void
     */
    public function getCommand($remoteCommand = '')
    {
        $command = $this->getConnectionCommand();

        if (!empty($remoteCommand)) {
            $command .= " '{$remoteCommand}'";
        }

        return $command;
    }

    /**
     * @return int|bool
     */
    public function ping()
    {
        return $this->mersey
            ->ping
            ->setHost($this->getHostname())
            ->setPort($this->getSshPort())
            ->ping();
    }

    /**
     * @return bool
     */
    public function isAccessible()
    {
        return $this->ping() ? true : false;
    }

    /**
     * @return string
     */
    protected function getConnectionCommand()
    {
        return sprintf("ssh -t -p %d -i %s %s@%s",$this->getSshPort(), $this->getSshKey(), $this->getUsername(), $this->getHostname());
    }

    /**
     * Execute a terminal command.
     *
     * @param string $command
     * @return bool|void
     */
    protected function execute($command)
    {
        return $this->passthru($command);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return array
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param Project $project
     */
    public function addProject(Project $project)
    {
        $this->projects[$project->getName()] = $project;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasProject($name)
    {
        return $this->projects->first(function($key, Project $project) use ($name) {
            return $project->getName() == $name;
        }) ? true : false ;
    }

    /**
     * @param $name
     * @return \Weeks\Mersey\Project
     */
    public function getProject($name)
    {
        return $this->projects->first(function($key, Project $project) use ($name) {
            return $project->getName() == $name;
        });
    }

    /**
     * @return string
     */
    public function getSshKey()
    {
        return $this->sshKey;
    }

    /**
     * @param string $sshKey
     * @return $this
     */
    public function setSshKey($sshKey)
    {
        $this->sshKey = $sshKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getSshPort()
    {
        return $this->sshPort;
    }

    /**
     * @param int $sshPort
     * @return $this
     */
    public function setSshPort($sshPort)
    {
        $this->sshPort = $sshPort;

        return $this;
    }
}