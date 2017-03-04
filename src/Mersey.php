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
use Weeks\Mersey\Services\Ping;

class Mersey extends Container
{
    /**
     * @var Application
     */
    protected $console;

    /**
     * @var Ping
     */
    public $ping;

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
     * @param Application $console
     * @param Ping $ping
     */
    public function __construct(Application $console, Ping $ping)
    {
        $this->console = $console;
        $this->ping = $ping;

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

            $command = new ServerCommand('server:' . $server->getName());
            $command->setDescription(sprintf('Connect to %s.', $server->getDisplayName()));
            $command->setServer($server);
            $this->console->add($command);
        }
    }

    /**
     * @param $serverConfig
     * @return Server
     */
    protected function createServer($serverConfig)
    {
        return new Server($this, $serverConfig);
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
     * @param $e
     */
    public function renderException($e)
    {
        $this->console->renderException($e, new ConsoleOutput());
        die();
    }

    public function getServersConfig($env)
    {
        return $this->getConfig($env, 'servers', 'servers.json');
    }

    public function getScriptsConfig($env)
    {
        return $this->getConfig($env, 'scripts', 'scripts.json');
    }

    public function loadServerConfig($env)
    {
        return $this->loadConfig('servers', $this->getServersConfig($env));
    }

    public function loadScriptConfig($env)
    {
        $config = $this->getConfig($env, 'scripts', 'scripts.json');

        return $this->loadConfig('scripts', $config);
    }

    private function getConfig($env, $type, $fileName)
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

    private function loadConfig($type, $fileName)
    {
        if (isset($this->configs[$type])) {
            return $this->configs[$type];
        }

        $json = file_exists($fileName) ? file_get_contents($fileName) : '[]';

        $this->configs[$type] = json_decode($json);

        return $this->configs[$type];
    }

    public function updateConfig($file, array $config)
    {
        return file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT));
    }

    public function getGlobalScripts()
    {
        return $this->scripts->toArray();
    }
}