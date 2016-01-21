<?php

namespace Weeks\Mersey\Commands;

use \Mockery as m;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Weeks\Mersey\Factories\ProjectFactory;
use Weeks\Mersey\Factories\ServerFactory;
use Weeks\Mersey\Mersey;
use Weeks\Mersey\Server;
use Weeks\Mersey\Services\JsonValidator;

class AvailableServerCommandTest extends \TestCase
{
    /**
    * @test
    */
    public function it_executes_the_command()
    {
        $mersey = m::mock(Mersey::class);
        $server = m::mock(Server::class);

        $server->shouldReceive('getDisplayName')->andReturn('server name');
        $server->shouldReceive('getName')->andReturn('server alias');
        $server->shouldReceive('ping')->andReturn(123);

        $mersey->shouldReceive('getServers')->andReturn([$server]);

        $application = new Application();
        $application->add(new AvailableServersCommand('ping', $mersey));

        $command = $application->find('ping');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $output = $commandTester->getDisplay();

        $this->assertContains('server name', $output);
        $this->assertContains('123', $output);
    }
}