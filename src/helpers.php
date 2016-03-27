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