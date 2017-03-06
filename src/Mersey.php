<?php

namespace Weeks\Mersey;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Weeks\Mersey\Commands\AddServerCommand;
use Weeks\Mersey\Commands\AvailableServersCommand;
use Weeks\Mersey\Commands\EditScriptsCommand;
use Weeks\Mersey\Commands\EditServersCommand;
use Weeks\Mersey\Commands\ServerCommand;
use Illuminate\Container\Container;
use Weeks\Mersey\Components\Script;
use Weeks\Mersey\Components\Server;
use Weeks\Mersey\Services\Ping\PingInterface;

class Mersey extends Container
{
    /**
     * @var Application
     */
    protected $console;

    /**
     * @var PingInterface
     */
    protected $ping;

    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @var Collection
     */
    protected $scripts;

    /**
     * @var Collection
     */
    protected $servers;

    /**
     * Mersey constructor.
     *
     * @param Application $console
     */
    public function __construct(Application $console)
    {
        $this->console = $console;

        /**
         * Register Mersey Commands
         */
        $this->console->add(new AvailableServersCommand($this));
        $this->console->add(new EditServersCommand($this));
        $this->console->add(new EditScriptsCommand($this));
        $this->console->add(new AddServerCommand($this));

        $this->servers = collect();
        $this->scripts = collect();
    }

    /**
     * Register all servers listed in the config
     *
     * @param $serversConfig
     */
    public function registerServers($serversConfig)
    {
        foreach ($serversConfig as $serverConfig) {
            $server = $this->createServer($serverConfig);
            $this->servers->push($server);

            $command = new ServerCommand($this, 'server:' . $server->getName());
            $command->setDescription(sprintf('Connect to %s.', $server->getDisplayName()));
            $command->setServer($server);
            $this->console->add($command);
        }
    }

    /**
     * Create a server from config
     *
     * @param $serverConfig
     *
     * @return Server
     */
    protected function createServer($serverConfig)
    {
        return new Server($serverConfig, $this->getGlobalScripts());
    }

    /**
     * Register scripts from the global script config.
     *
     * @param $scripts
     */
    public function registerGlobalScripts($scripts)
    {
        foreach ($scripts as $script) {
            $this->scripts->push(new Script($script));
        }
    }

    /**
     * Run the Mersey app
     *
     * @throws \Exception
     */
    public function run()
    {
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
     *
     * @param $e
     */
    public function renderException(\Exception $e)
    {
        $this->console->renderException($e, new ConsoleOutput());
        die();
    }

    /**
     * Get the servers config file path.
     *
     * @param $env
     *
     * @return string
     */
    public function getServersConfig($env)
    {
        return $this->getConfig($env, 'servers', 'servers.json');
    }

    /**
     * Get the scripts config file path.
     *
     * @param $env
     *
     * @return string
     */
    public function getScriptsConfig($env)
    {
        return $this->getConfig($env, 'scripts', 'scripts.json');
    }

    /**
     * Get the contents of the server config file as json.
     *
     * @param $env
     *
     * @return mixed
     */
    public function loadServerConfig($env)
    {
        return $this->loadConfig('servers', $this->getServersConfig($env));
    }

    /**
     * Load the contents of the script config file.
     *
     * @param $env
     *
     * @return mixed
     */
    public function loadScriptConfig($env)
    {
        $config = $this->getConfig($env, 'scripts', 'scripts.json');

        return $this->loadConfig('scripts', $config);
    }

    /**
     * Get the path of a config file.
     *
     * @param $env
     * @param $type
     * @param $fileName
     *
     * @return string
     */
    protected function getConfig($env, $type, $fileName)
    {
        $configPath = env('HOME') . '/.mersey/' . $fileName;

        if ($env == 'testing') {
            $configPath = "tests/fixtures/{$type}/valid.json";
        }

        if (!file_exists($configPath) || $env == 'local') {
            $configPath = $fileName;
        }

        return $configPath;
    }

    /**
     * Load a config file.
     *
     * @param $type
     * @param $fileName
     *
     * @return mixed
     */
    protected function loadConfig($type, $fileName)
    {
        if (isset($this->configs[$type])) {
            return $this->configs[$type];
        }

        $json = file_exists($fileName) ? file_get_contents($fileName) : '[]';

        $this->configs[$type] = json_decode($json);

        return $this->configs[$type];
    }

    /**
     * Update a config file
     *
     * @param       $file
     * @param array $config
     *
     * @return int
     */
    public function updateConfig($file, array $config)
    {
        return file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Get the global scripts array.
     *
     * @return array
     */
    public function getGlobalScripts()
    {
        return $this->scripts->toArray();
    }

    /**
     * Ping a server
     *
     * @param Server $server
     *
     * @return integer
     */
    public function pingServer(Server $server)
    {
        return $this->make(PingInterface::class)
            ->setHost($server->getHostname())
            ->setPort($server->getSshPort())
            ->ping();
    }

    /**
     * Test if a server is accessible.
     *
     * @param Server $server
     *
     * @return bool
     */
    public function serverIsAccessible(Server $server)
    {
        return $this->pingServer($server) ? true : false;
    }
}