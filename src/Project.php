<?php

namespace Weeks\Mersey;

use Illuminate\Support\Collection;

class Project
{
    /**
     * The name of the projects
     *
     * @var string
     */
    protected $name;

    /**
     * The root directory of the project
     *
     * @var string
     */
    protected $root;

    /**
     * Collection of scripts available to this project.
     *
     * @var Collection
     */
    protected $scripts;

    /**
     * Project constructor.
     *
     * @param $config
     * @param $globalScripts
     */
    public function __construct($config, $globalScripts)
    {
        $this->name = $config->name;
        $this->root = $config->root;

        $localScripts = isset($config->scripts) ? $this->loadScripts($config->scripts) : collect();

        $this->scripts = collect(array_merge($localScripts->toArray(), $globalScripts));
    }

    /**
     * Get the root project command
     *
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

    /**
     * Get the scripts
     *
     * @return Collection
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Get the command of a registered script.
     *
     * @param $name
     *
     * @return Script
     */
    public function getScript($name)
    {
        return $this->scripts->first(function ($key, $item) use ($name) {
            return $item->name == $name;
        });
    }

    /**
     * Create collection of Script objects.
     *
     * @param $scripts
     *
     * @return Collection
     */
    protected function loadScripts($scripts)
    {
        $collection = collect();

        foreach ($scripts as $script) {
            $collection->push(new Script($script));
        }

        return $collection;
    }
}