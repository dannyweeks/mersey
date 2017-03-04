<?php

namespace Weeks\Mersey;

class Script
{
    public $name, $description, $command;

    /**
     * Additional commands after a user has ran a script has ran.
     */
    const SCRIPT_COMMAND = "; read -p \"Remote script completed. Press enter to continue..\"; exit";

    public function __construct($scriptConfig)
    {
        $this->name = $scriptConfig->name;
        $this->description = $scriptConfig->description;
        $this->command = $scriptConfig->command;
    }

    public function getCommand()
    {
        return $this->command . self::SCRIPT_COMMAND;
    }
}