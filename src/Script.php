<?php

namespace Weeks\Mersey;

class Script
{
    /**
     * The name of this script
     *
     * @var string
     */
    public $name;

    /**
     * The description of this script
     *
     * @var string
     */
    public $description;

    /**
     * The command for this script
     *
     * @var string
     */
    public $command;

    /**
     * Additional commands after a user has ran a script has ran.
     */
    const SCRIPT_COMMAND = "; read -p \"Remote script completed. Press enter to continue..\"; exit";

    /**
     * Script constructor.
     *
     * @param $scriptConfig
     */
    public function __construct($scriptConfig)
    {
        $this->name = $scriptConfig->name;
        $this->description = $scriptConfig->description;
        $this->command = $scriptConfig->command;
    }

    /**
     * Get the fully qualified command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command . self::SCRIPT_COMMAND;
    }
}