<?php

namespace Weeks\Mersey\Commands;

use \Mockery as m;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Weeks\Mersey\Factories\ProjectFactory;
use Weeks\Mersey\Factories\ServerFactory;
use Weeks\Mersey\Mersey;
use Weeks\Mersey\Project;
use Weeks\Mersey\Server;
use Weeks\Mersey\Services\JsonValidator;

class ServerCommandTest extends \TestCase
{
    /**
     * @test
     */
    public function it_connects_to_the_server()
    {
        $application = new Application();
        $application->add(new ServerCommand('testserver'));

        $command = $application->find('testserver');

        $server = m::mock(Server::class);
        $server->shouldReceive('isAccessible')->andReturn(true);
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');
        $server->shouldReceive('getProject')->andReturn([]);
        $expectedCommand = 'server connect command';
        $server->shouldReceive('getCommand')->andReturn($expectedCommand);
        $command->setServer($server);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'    => $command->getName(),
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        $output = $commandTester->getDisplay();

        $this->assertCommandExecuted($expectedCommand, $output);
    }

    /**
    * @test
    */
    public function it_displays_the_servers_projects()
    {
        $application = new Application();
        $application->add(new ServerCommand('testserver'));

        $command = $application->find('testserver');

        $server = m::mock(Server::class);
        $project = m::mock(Project::class);
        $server->shouldReceive('getProjects')->andReturn([
            $project
        ]);

        $server->shouldReceive('getName')->andReturn('testserver');
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');
        $project->shouldReceive('getName')->andReturn('projectname');


        $command->setServer($server);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--projects' => null,
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains('Available projects for Test Server', $output);
        $this->assertContains('projectname', $output);
    }

    /**
    * @test
    */
    public function it_displays_error_if_the_server_not_available()
    {
        $application = new Application();
        $application->add(new ServerCommand('testserver'));

        $command = $application->find('testserver');

        $server = m::mock(Server::class);
        $server->shouldReceive('isAccessible')->andReturn(false);
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');

        $command->setServer($server);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains('Test Server is unreachable', $output);
    }

    /**
    * @test
    */
    public function it_connects_to_a_project()
    {
        $application = new Application();
        $application->add(new ServerCommand('testserver'));

        $command = $application->find('testserver');

        $server = m::mock(Server::class);
        $project = m::mock(Project::class);
        $server->shouldReceive('setDebug')->andReturnSelf();
        $server->shouldReceive('isAccessible')->andReturn(true);
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');
        $server->shouldReceive('hasProject')->andReturn(true);
        $server->shouldReceive('getProject')->andReturn($project);
        $project->shouldReceive('getName')->andReturn('testproject');
        $project->shouldReceive('getRootCommand')->andReturn('root command');
        $expectedCommand = 'project connect command';
        $server->shouldReceive('getCommand')->andReturn($expectedCommand);

        $command->setServer($server);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'project' => 'testproject'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        $output = $commandTester->getDisplay();

        $this->assertCommandExecuted($expectedCommand, $output);
    }
    /**
     * @test
     */
    public function it_shows_error_when_project_doesnt_exist()
    {
        $application = new Application();
        $application->add(new ServerCommand('testserver'));

        $command = $application->find('testserver');

        $server = m::mock(Server::class);
        $server->shouldReceive('isAccessible')->andReturn(true);
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');
        $server->shouldReceive('hasProject')->andReturn(false);

        $command->setServer($server);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'project' => 'fakeproject'
        ]);

        $output = $commandTester->getDisplay();

        $this->assertRegExp('/there is no project/i', $output);
    }

    /**
    * @test
    */
    public function it_executes_a_remote_script()
    {
        $application = new Application();
        $application->add(new ServerCommand('testserver'));

        $command = $application->find('testserver');

        $server = m::mock(Server::class);
        $project = m::mock(Project::class);
        $server->shouldReceive('setDebug')->andReturnSelf();
        $server->shouldReceive('isAccessible')->andReturn(true);
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');
        $server->shouldReceive('hasProject')->andReturn(true);
        $server->shouldReceive('getProject')->andReturn($project);
        $project->shouldReceive('availableScripts')->andReturn(['testscript']);
        $project->shouldReceive('getScript')->andReturn('cd /a/script/');
        $expectedCommand = 'remote script ran';
        $server->shouldReceive('getCommand')->andReturn($expectedCommand);

        $command->setServer($server);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'project' => 'testproject',
            'script' => 'testscript'
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        $output = $commandTester->getDisplay();

        $this->assertCommandExecuted($expectedCommand, $output);
    }


    /**
     * @test
     */
    public function it_shows_error_when_the_script_doesnt_exist()
    {
        $application = new Application();
        $application->add(new ServerCommand('testserver'));

        $command = $application->find('testserver');

        $server = m::mock(Server::class);
        $project = m::mock(Project::class);
        $server->shouldReceive('isAccessible')->andReturn(true);
        $server->shouldReceive('getDisplayName')->andReturn('Test Server');
        $server->shouldReceive('hasProject')->andReturn(true);
        $server->shouldReceive('getProject')->andReturn($project);
        $project->shouldReceive('availableScripts')->andReturn(['testscript']);

        $command->setServer($server);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'project' => 'testproject',
            'script' => 'fakescript'
        ]);

        $output = $commandTester->getDisplay();

        $this->assertRegExp('/there is no script/i', $output);
    }
}