<?php

namespace Weeks\Mersey\Services;

interface PingInterface {
    public function ping();
    public function setHost($host);
    public function setTtl($ttl);
    public function setPort($port);
}