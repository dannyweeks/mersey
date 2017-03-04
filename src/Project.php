<?php

namespace Weeks\Mersey;

use Illuminate\Support\Collection;

class Project
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $root;

    /**
     * @var Collection
     */
    private $scripts;

    public function __construct($config, $globalScripts)
    {
        $this->name = $config->name;
        $this->root = $config->root;

        $localScripts = isset($config->scripts) ? $this->loadScripts($config->scripts) : collect();

        $this->scripts = collect(array_merge($localScripts->toArray(), $globalScripts));
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
        return $this->scripts->pluck('name')->toArray();
    }

    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Get the command of a registered script.
     *
     * @param $name
     * @return Script
     */
    public function getScript($name)
    {
        return $this->scripts->first(function ($key, $item) use ($name) {
            return $item->name == $name;
        });
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasScript($name)
    {
        return in_array($name, $this->availableScripts());
    }

    private function loadScripts($scripts)
    {
        $collection = collect();

        foreach ($scripts as $script) {
            $collection->push(new Script($script));
        }

        return $collection;
    }

}