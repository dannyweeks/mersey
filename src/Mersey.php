<?php

namespace Weeks\Mersey;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Weeks\Mersey\Commands\AvailableServersCommand;
use Weeks\Mersey\Commands\EditServersCommand;
use Weeks\Mersey\Commands\HelpCommand;
use Weeks\Mersey\Commands\ServerCommand;
use Weeks\Mersey\Exceptions\IllegalScriptNameException;
use Weeks\Mersey\Exceptions\IllegalServerNameException;
use Weeks\Mersey\Exceptions\InvalidServerConfigException;
use Weeks\Mersey\Factories\ProjectFactory;
use Weeks\Mersey\Factories\ServerFactory;
use Weeks\Mersey\Services\JsonValidator;

class Mersey
{
    protected $loadedServers;

    /**
     * @var Application
     */
    private $console;

    /**
     * Reserved command words
     *
     * @var array
     */
    protected $protectedCommandNames = ['help', 'list'];

    protected $protectedOptionNames = [
        'projects',
        'help',
        'quiet',
        'version',
        'ansi',
        'verbose',
        'no-ansi',
        'no-interaction'
    ];
    /**
     * @var JsonValidator
     */
    private $jsonValidator;
    /**
     * @var ServerFactory
     */
    private $serverFactory;
    /**
     * @var ProjectFactory
     */
    private $projectFactory;

    public function __construct(Application $console, JsonValidator $jsonValidator, ServerFactory $serverFactory, ProjectFactory $projectFactory)
    {
        $this->console = $console;
        $this->jsonValidator = $jsonValidator;

        $this->console->add(new AvailableServersCommand('ping', $this));
        $this->console->add(new EditServersCommand());
        $this->servers = new Collection();
        $this->serverFactory = $serverFactory;
        $this->projectFactory = $projectFactory;
    }

    /**
     * Loads the servers from json.
     *
     * @param $serversJson
     */
    public function loadServersFromJson($serversJson)
    {

        $servers = json_decode(file_get_contents($serversJson));

        $this->validateServerJsonSchema($serversJson, $servers);

        $this->ensureNoReservedCommandWordsUsed($servers);

        $this->loadedServers = $servers;
    }

    /**
     * Run the Mersey app
     *
     * @throws \Exception
     */
    public function run()
    {
        foreach ($this->loadedServers as $server) {

            $serverInstance = $this->registerServer($server);

            $command = new ServerCommand($server->name);
            $command->setDescription(sprintf('Connect to %s.', $server->displayName));
            $command->setServer($serverInstance);
            $this->console->add($command);
        }

        $this->console->run();
    }

    /**
     * @return Collection
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * Renders a caught exception.
     * @param $e
     */
    public function renderException($e)
    {
        $this->console->renderException($e, new ConsoleOutput());
    }

    /**
     * Validate the user servers against the schema.
     *
     * @param $serverJsonFile
     * @param $parsedServers
     *
     * @throws InvalidServerConfigException
     */
    private function validateServerJsonSchema($serverJsonFile, $parsedServers)
    {
        if (!$this->jsonValidator->validate($parsedServers)) {
            $exceptionMessage = $serverJsonFile . " is not valid. Violations:\n";
            foreach ($this->jsonValidator->getErrors() as $error) {
                $exceptionMessage .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new InvalidServerConfigException($exceptionMessage);
        }
    }

    /**
     * Check user hasn't tried to use a server name that is already used as a command.
     *
     * @param $servers
     *
     * @throws IllegalServerNameException
     */
    private function ensureNoReservedCommandWordsUsed($servers)
    {
        $serversNames = array_pluck($servers, 'name');

        foreach ($this->protectedCommandNames as $protected) {
            if (in_array($protected, $serversNames)) {
                $format = "'%s' is reserved and cannot be used as a server name. Please use another name.";
                throw new IllegalServerNameException(sprintf($format, $protected));
            }
        }
    }

    /**
     * @param $server
     * @return Server
     */
    private function registerServer($server)
    {
        $serverInstance = $this->serverFactory->create(
            $server->name,
            $server->username,
            $server->hostname,
            $server->displayName
        );

        if (!empty($server->sshKey)) {
            $serverInstance->setSshKey($server->sshKey);
        }

        if (!empty($server->port)) {
            $serverInstance->setSshPort($server->port);
        }

        if ($this->serverHasProjects($server)) {

            foreach ($server->projects as $project) {

                $scripts = isset($project->scripts) ? (array) $project->scripts : [];

                $projectInstance = $this->projectFactory->create(
                    $project->name,
                    $project->root,
                    $scripts
                );

                $serverInstance->addProject($projectInstance);
            }
        }

        $this->servers->push($serverInstance);

        return $serverInstance;
    }

    /**
     * @param $server
     * @return bool
     */
    private function serverHasProjects($server)
    {
        return isset($server->projects) && is_array($server->projects);
    }
}