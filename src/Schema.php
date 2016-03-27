<?php

namespace Weeks\Mersey;

class Schema
{
    protected $env;
    protected $home;
    private $fileName;

    public function __construct($fileName, $env, $home)
    {
        $this->env = $env;
        $this->home = $home;
        $this->fileName = $fileName;
    }

    public function resolve()
    {
        $globalSchema = $this->home . '/.composer/vendor/dannyweeks/mersey/' . $this->fileName;

        if (!file_exists($globalSchema) || $this->env == 'local') {
            return $this->fileName;
        }

        return $globalSchema;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}