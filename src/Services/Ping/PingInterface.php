<?php

namespace Weeks\Mersey\Services\Ping;

interface PingInterface {
    public function ping($method);
    public function setHost($host);
    public function setTtl($ttl);
    public function setPort($port);
}