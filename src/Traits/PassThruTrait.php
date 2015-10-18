<?php

namespace Weeks\Mersey\Traits;

trait PassThruTrait {
    /**
     * Execute terminal command.
     *
     * @param $command
     * @return mixed|void
     */
    protected function passthru($command){
        return passthru($command);
    }
}