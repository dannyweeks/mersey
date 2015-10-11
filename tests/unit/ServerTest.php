<?php

namespace Weeks\Mersey;

use \Mockery as m;

class ServerTest extends \TestCase
{
    /**
     * @test
     */
    public function it_creates_a_full_connection_command()
    {
        $this->assertEquals('ssh -t -i ~/.ssh/id_rsa danny@192.168.0.1', $this->getServer()->connect());
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
    public function it_tries_to_ping_the_server()
    {
        $server = $this->getServer();

        $this->assertEquals(10, $server->ping());
    }

    /**
     * @return Server
     */
    private function getServer(){

        $pingMock = m::mock(\Weeks\Mersey\Services\Ping::class);
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


/**
 * Overwrite the php passthru function
 *
 * @param $command
 * @return mixed
 */
function passthru($command)
{
    return $command;
}
