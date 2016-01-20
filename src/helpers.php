<?php

if (!function_exists('env')) {
    function env($envName, $default = null)
    {
        $env = getenv($envName);

        if ($env === false) {
            return $default;
        }

        return $env;
    }
}

function getSchema($homeDir, $env = null)
{
    $globalSchema = $homeDir . '/.composer/vendor/dannyweeks/mersey/schema.json';

    if (!file_exists($globalSchema) || $env == 'local') {
        return 'schema.json';
    }

    return $globalSchema;
}

function getServerConfig($homeDir, $env = null) {
    $globalServers = $homeDir . '/.mersey/servers.json';

    if($env == 'testing') {
        return 'tests/data/valid.json';
    }

    if (!file_exists($globalServers) || $env == 'local') {
        return 'servers.json';
    }

    return $globalServers;
}