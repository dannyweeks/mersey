<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;
use Weeks\Mersey\Server;

class AvailableServersCommandTest extends TestCase
{
    /**
     * @test
     */
    public function execute_displays_the_servers()
    {
        $mersey = $this->getMerseyMock();

        $server = m::mock(Server::class);
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');
        $server->shouldReceive('getName')->andReturn('test');
        $server->shouldReceive('ping')->andReturn(1234);

        $mersey->shouldReceive('getServers')->andReturn(collect([$server]));

        $command = $this->getApplication($mersey)->find('ping');

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $output = $tester->getDisplay();

        $this->assertRegExp("/Test Server/", $output);
        $this->assertRegExp("/test/", $output);
        $this->assertRegExp("/1234/", $output);

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    private function getApplication($mersey)
    {
        $app = new Application();
        $app->add(new \Weeks\Mersey\Commands\AvailableServersCommand($mersey));

        return $app;
    }
}