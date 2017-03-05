<?php

namespace Weeks\Mersey;

class Schema
{
    /**
     * The app environment
     *
     * @var string
     */
    protected $env;

    /**
     * Path the user's home
     *
     * @var string
     */
    protected $home;

    /**
     * The schema file name
     *
     * @var string
     */
    protected $fileName;

    /**
     * Schema constructor.
     *
     * @param $fileName
     * @param $env
     * @param $home
     */
    public function __construct($fileName, $env, $home)
    {
        $this->env = $env;
        $this->home = $home;
        $this->fileName = $fileName;
    }

    /**
     * Resolve the path to the schema.
     *
     * @return string
     */
    public function resolve()
    {
        $globalSchema = $this->home . '/.composer/vendor/dannyweeks/mersey/' . $this->fileName;

        if (!file_exists($globalSchema) || $this->env == 'local') {
            return $this->fileName;
        }

        return $globalSchema;
    }

    /**
     * Get the file name.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}