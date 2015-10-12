<?php

namespace Weeks\Mersey\Services;

class Ping extends \JJG\Ping implements PingInterface
{
    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        parent::setHost($host);

        return $this;
    }

    /**
     * @param int $port
     * @return $this
     */
    public function setPort($port)
    {
        parent::setPort($port);

        return $this;
    }

    /**
     * @param int $ttl
     * @return $this
     */
    public function setTtl($ttl)
    {
        parent::setTtl($ttl);

        return $this;
    }
}