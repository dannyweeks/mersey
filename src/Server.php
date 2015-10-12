<?php

namespace Weeks\Mersey;


use Weeks\Mersey\Services\Ping;

class Server
{
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
    private $sshKey = '~/.ssh/id_rsa';
    /**
     * @var integer
     */
    private $sshPort = 22;


    /**
     * @param Services\Ping $pingService
     * @param $name
     * @param $username
     * @param $hostname
     * @param $displayName
     * @param array $projects
     */
    public function __construct(Ping $pingService, $name, $username, $hostname, $displayName, $projects = [])
    {
        $this->pingService = $pingService;

        $this->name = $name;
        $this->username = $username;
        $this->hostname = $hostname;
        $this->displayName = $displayName;
        $this->projects = $projects;

        $this->pingService
            ->setHost($this->hostname)
            ->setPort($this->sshPort)
            ->setTtl(2);
    }

    /**
     * Connect to the server via SSH.
     *
     * @param string $remoteCommand
     * @return bool|void
     */
    public function connect($remoteCommand = '')
    {
        $command = $this->getConnectionCommand();

        if (!empty($remoteCommand)) {
            $command .= " '{$remoteCommand}'";
        }

        return $this->execute($command);
    }

    /**
     * @return int|bool
     */
    public function ping()
    {
        return $this->pingService->ping();
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
        return sprintf("ssh -t -i %s %s@%s", $this->getSshKey(), $this->getUsername(), $this->getHostname());
    }

    /**
     * Execute a terminal command.
     *
     * @param string $command
     * @return bool|void
     */
    protected function execute($command)
    {
        return passthru($command);
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
        return array_key_exists($name, $this->projects);
    }

    /**
     * @param $name
     * @return \Weeks\Mersey\Project
     */
    public function getProject($name)
    {
        return array_get($this->projects, $name);
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
}