<?php

namespace Weeks\Mersey\Components;

class Environment
{
    /**
     * @var string
     */
    protected $envString;

    /**
     * Environment constructor.
     *
     * @param $envString string
     */
    public function __construct($envString)
    {
        $this->envString = $envString;
    }

    public function getMerseyHome()
    {

    }

    public function getComposerHome()
    {
        $home = getenv('COMPOSER_HOME');
        if ($home) {
            return $home;
        }

        $userDir = self::getUserDir();
        if (is_dir($userDir . '/.composer')) {
            return $userDir . '/.composer';
        }
        if (self::useXdg()) {
            // XDG Base Directory Specifications
            $xdgConfig = getenv('XDG_CONFIG_HOME') ?: $userDir . '/.config';
            return $xdgConfig . '/composer';
        }
        return $userDir . '/.composer';
    }

    /**
     * @return bool
     */
    private static function useXdg()
    {
        foreach (array_keys($_SERVER) as $key) {
            if (substr($key, 0, 4) === 'XDG_') {
                return true;
            }
        }
        return false;
    }
}