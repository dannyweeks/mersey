<?php

namespace Weeks\Mersey;

class ConsoleTest extends \TestCase
{

    /**
    * @test
    */
    public function it_is_symfony_console()
    {
        $console = new Console();

        $this->assertInstanceOf('Symfony\Component\Console\Application', $console);
    }

}