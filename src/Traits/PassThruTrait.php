<?php

namespace Weeks\Mersey\Traits;

trait PassThruTrait
{
    /**
     * Execute terminal command.
     *
     * @param $command
     * @return mixed|void
     */
    protected function passthru($command)
    {
        if (property_exists($this, 'output') && $this->output->isDebug()) {
            $this->output->writeln('<comment>DEBUG: Executing command \'' . $command . '\'</comment>');
        }

        if (env('APP_ENV') == 'testing') {

            return $command;
        }

        return passthru($command);
    }
}