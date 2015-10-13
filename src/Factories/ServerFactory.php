<?php


namespace Weeks\Mersey\Factories;


use Weeks\Mersey\Server;
use Weeks\Mersey\Services\Ping;

class ServerFactory {
    /**
     * @param $name
     * @param $username
     * @param $hostname
     * @param $displayName
     * @param array $projects
     * @return Server
     */
    public function create($name, $username, $hostname, $displayName, $projects = [])
    {
        return new Server(new Ping($hostname), $name, $username, $hostname, $displayName, $projects);
    }
}