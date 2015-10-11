<?php

namespace Weeks\Mersey\Services;

class Ping extends \JJG\Ping implements PingInterface
{
    public function setHost($host)
    {
        parent::setHost($host);

        return $this;
    }

    public function setPort($port)
    {
        parent::setPort($port);

        return $this;
    }

    public function setTtl($ttl)
    {
        parent::setTtl($ttl);

        return $this;
    }
}