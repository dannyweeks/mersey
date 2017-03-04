<?php

namespace Weeks\Mersey\Services\Ping;

use JJG\Ping;

class JJGPing extends Ping implements PingInterface
{
    /**
     * Perform a ping
     *
     * @param string $method
     *
     * @return mixed
     */
    public function ping($method = 'fsockopen')
    {
        return parent::ping($method);
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        parent::setHost($host);

        return $this;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        parent::setPort($port);

        return $this;
    }

    /**
     * @param int $ttl
     *
     * @return $this
     */
    public function setTtl($ttl)
    {
        parent::setTtl($ttl);

        return $this;
    }
}