<?php

namespace Weeks\Mersey;

use Illuminate\Support\Collection;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Weeks\Mersey\Commands\AvailableServersCommand;
use Weeks\Mersey\Commands\HelpCommand;
use Weeks\Mersey\Commands\ServerCommand;
use Weeks\Mersey\Exceptions\IllegalScriptNameException;
use Weeks\Mersey\Exceptions\IllegalServerNameException;
use Weeks\Mersey\Exceptions\InvalidServerConfigException;
use Weeks\Mersey\Services\JsonValidator;
use Weeks\Mersey\Services\Ping;

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

    public function __construct(Application $console, JsonValidator $jsonValidator)
    {
        $this->console = $console;
        $this->console->add(new AvailableServersCommand('ping', $this));
        $this->servers = new Collection();
        $this->jsonValidator = $jsonValidator;
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
            $command = new ServerCommand($server->name);
            $command->setDescription(sprintf('Connect to %s.', $server->displayName));

            $serverInstance = new Server(
                new Ping($server->hostname),
                $server->name,
                $server->username,
                $server->hostname,
                $server->displayName
            );

            if (!empty($server->sshKey)) {
                $serverInstance->setSshKey($server->sshKey);
            }

            if (isset($server->projects) && is_array($projects = $server->projects)) {

                foreach ($projects as $project) {
                    $scripts = (array)$project->scripts;

                    $scripts = !empty($scripts) ? $scripts : [];

                    $projectInstance = new Project(
                        $project->name,
                        $project->root,
                        $scripts
                    );

                    $serverInstance->addProject($projectInstance);
                }
            }

            $command->setServer($serverInstance);
            $this->servers->push($serverInstance);

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
}