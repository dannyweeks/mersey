<?php

namespace Weeks\Mersey;

use Weeks\Mersey\Traits\PassThruTrait;

class Server
{
    use PassThruTrait;

    /**
     * Name of the server
     *
     * @var string
     */
    protected $name;

    /**
     * SSH Username
     *
     * @var string
     */
    protected $username;

    /**
     * IP or hostname of the server
     *
     * @var string
     */
    protected $hostname;

    /**
     * Name of server to be show in UI
     *
     * @var string
     */
    protected $displayName;

    /**
     * Array of projects associated to this server
     *
     * @var Project[]
     */
    protected $projects;

    /**
     * Path to the SSH key to be used for accessing this server
     *
     * @var string
     */
    protected $sshKey;

    /**
     * Port to access this server on
     *
     * @var integer
     */
    protected $sshPort;

    /**
     * Server constructor.
     *
     * @param string $config the config of this server
     * @param        $globalScripts
     */
    public function __construct($config, $globalScripts)
    {
        $this->name = $config->name;
        $this->username = $config->username;
        $this->hostname = $config->hostname;
        $this->displayName = $config->displayName;
        $this->sshPort = isset($config->port) ? $config->port : 22;
        $this->sshKey = isset($config->sshKey) ? $config->sshKey : '~/.ssh/id_rsa';

        $this->projects = collect();
        if (isset($config->projects)) {
            foreach ($config->projects as $projectConfig) {
                $this->projects->push(new Project($projectConfig, $globalScripts));
            }
        }
    }

    /**
     * Get the full SSH command tailored to this servers config.
     *
     * @param string $remoteCommand
     *
     * @return string
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
     * Get the command used to connect to a server.
     *
     * -t allow interaction
     * -p define the port to connect on
     * -i define the identity file
     *
     * @return string
     */
    protected function getConnectionCommand()
    {
        return sprintf(
            "ssh -t -p %d -i %s %s@%s",
            $this->getSshPort(),
            $this->getSshKey(),
            $this->getUsername(),
            $this->getHostname()
        );
    }

    /**
     * Execute a terminal command.
     *
     * @param string $command
     *
     * @return bool|void
     */
    protected function execute($command)
    {
        return $this->passthru($command);
    }

    /**
     * Get the server name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the ssh username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Get the UI server name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Get the server projects
     *
     * @return array
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Add a project to this server.
     *
     * @param Project $project
     */
    public function addProject(Project $project)
    {
        $this->projects[$project->getName()] = $project;
    }

    /**
     * Check if this server has a project by the name
     *
     * @param $name
     *
     * @return bool
     */
    public function hasProject($name)
    {
        return $this->projects->first(function ($key, Project $project) use ($name) {
            return $project->getName() == $name;
        }) ? true : false;
    }

    /**
     * Get a project by it's name
     *
     * @param $name
     *
     * @return Project
     */
    public function getProject($name)
    {
        return $this->projects->first(function ($key, Project $project) use ($name) {
            return $project->getName() == $name;
        });
    }

    /**
     * Get an array of the server's project names
     *
     * @return string[]
     */
    public function getProjectNames()
    {
        return $this->projects->map(function (Project $project) {
            return $project->getName();
        })->toArray();
    }

    /**
     * Get the SSH key path
     *
     * @return string
     */
    public function getSshKey()
    {
        return $this->sshKey;
    }

    /**
     * Get the ssh port.
     *
     * @return int
     */
    public function getSshPort()
    {
        return $this->sshPort;
    }
}