<?php

namespace Weeks\Mersey;

use \Mockery as m;
use Weeks\Mersey\Services\Ping;

class ServerTest extends \TestCase
{
    /**
     * @test
     */
    public function it_creates_a_full_connection_command()
    {
        $this->assertEquals('ssh -t -p 22 -i ~/.ssh/id_rsa danny@192.168.0.1', $this->getServer()->connect());
    }

    /**
    * @test
    */
    public function it_sets_an_ssh_key()
    {
        $this->assertContains('ssh/private', $this->getServer()->setSshKey('ssh/private')->connect());
    }

    /**
    * @test
    */
    public function it_sets_an_ssh_port()
    {
        $this->assertContains('-p 123', $this->getServer()->setSshPort(123)->connect());
    }

    /**
    * @test
    */
    public function it_tries_to_ping_the_server()
    {
        $server = $this->getServer();

        $this->assertEquals(10, $server->ping());
    }

    /**
     * @return Server
     */
    private function getServer(){

        $pingMock = m::mock(Ping::class);
        $pingMock->shouldReceive('setHost')->andReturnSelf();
        $pingMock->shouldReceive('setPort')->andReturnSelf();
        $pingMock->shouldReceive('setTtl')->andReturnSelf();
        $pingMock->shouldReceive('ping')->andReturn(10);

        return new Server(
            $pingMock,
            'production',
            'danny',
            '192.168.0.1',
            'Production Server'
        );
    }
}