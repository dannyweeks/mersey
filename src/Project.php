<?php


namespace Weeks\Mersey;


class Project {
    /**
     * Additional commands after a user has ran a script has ran.
    */
    const SCRIPT_COMMAND= "; read -p \"Remote script completed. Press enter to continue..\"; exit";

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $root;
    /**
     * @var array
     */
    private $scripts;

    public function __construct($name, $root, $scripts = [])
    {
        $this->name = $name;
        $this->root = $root;
        $this->scripts = $scripts;
    }

    /**
     * @return string
     */
    public function getRootCommand()
    {
        return sprintf("cd %s && bash", $this->root);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Array of scripts registered to this project.
     *
     * @return array
     */
    public function availableScripts()
    {
        return array_keys($this->scripts);
    }

    /**
     * Get the command of a registered script.
     *
     * @param $name
     * @return string
     */
    public function getScript($name)
    {
        return $this->scripts[$name] . self::SCRIPT_COMMAND;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasScript($name)
    {
        return in_array($name, $this->availableScripts());
    }

}