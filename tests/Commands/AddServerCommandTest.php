<?php

use Ofbeaton\Console\Tester\QuestionTester;
use Ofbeaton\Console\Tester\UnhandledQuestionException;
use Symfony\Component\Console\Application;
use Mockery as m;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;

class AddServerCommandTest extends TestCase
{
    /**
     * @test
     */
    public function execute_creates_a_basic_server()
    {
        $mersey = $this->getLocalMerseyMock([
            [
                "name"        => "testing",
                "displayName" => "Test Display",
                "username"    => "testuser",
                "hostname"    => "example.com",
            ]
        ]);

        $command = $this->getApplication($mersey)->find('add');

        $this->mockAnswersUsingArray($command, [
            ['server name/alias', 'testing'],
            ['server display name', "Test Display"],
            ['SSH username', "testuser"],
            ['hostname', 'example.com'],
            ['optional settings', 'n'],
            ['define a project', 'n'],
        ]);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    /**
     * @test
     */
    public function execute_creates_a_server_with_additional_settings()
    {
        $mersey = $this->getLocalMerseyMock([
            [
                'name'        => 'testing',
                'displayName' => 'Test Display',
                'username'    => 'testuser',
                'hostname'    => 'example.com',
                'sshKey'      => 'special_key',
                'port'        => 3000,
            ]
        ]);

        $command = $this->getApplication($mersey)->find('add');

        $this->mockAnswersUsingArray($command, [
            ['server name/alias', 'testing'],
            ['server display name', 'Test Display'],
            ['SSH username', 'testuser'],
            ['hostname', 'example.com'],
            ['optional settings', 'y'],
            ['ssh key', 'special_key'],
            ['port', 3000],
            ['define a project', 'n']
        ]);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    /**
     * @test
     */
    public function execute_creates_a_server_with_project()
    {
        $mersey = $this->getLocalMerseyMock([
            [
                'name'        => 'testing',
                'displayName' => 'Test Display',
                'username'    => 'testuser',
                'hostname'    => 'example.com',
                'sshKey'      => 'special_key',
                'port'        => 3000,
                'projects'    => [
                    [
                        'name' => 'testproject',
                        'root' => '/project/root',
                    ]
                ],
            ]
        ]);

        $command = $this->getApplication($mersey)->find('add');

        $this->mockAnswersUsingArray($command, [
            ['server name/alias', 'testing'],
            ['server display name', 'Test Display'],
            ['SSH username', 'testuser'],
            ['hostname', 'example.com'],
            ['optional settings', 'y'],
            ['ssh key', 'special_key'],
            ['port', 3000],
            ['define a project', 'y'],
            ['project name', 'testproject'],
            ['project root', '/project/root'],
            ['scripts to the project', 'n'],
            ['another project', 'n']
        ]);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    /**
     * @test
     */
    public function execute_creates_a_server_with_project_and_scripts()
    {
        $mersey = $this->getLocalMerseyMock([
            [
                'name'        => 'testing',
                'displayName' => 'Test Display',
                'username'    => 'testuser',
                'hostname'    => 'example.com',
                'sshKey'      => 'special_key',
                'port'        => 3000,
                'projects'    => [
                    [
                        'name'    => 'testproject',
                        'root'    => '/project/root',
                        'scripts' => [
                            [
                                'name'        => 'testscript',
                                'description' => 'A Script',
                                'command'     => 'my script',
                            ]
                        ],
                    ]
                ],
            ]
        ]);

        $command = $this->getApplication($mersey)->find('add');

        $this->mockAnswersUsingArray($command, [
            ['server name/alias', 'testing'],
            ['server display name', 'Test Display'],
            ['SSH username', 'testuser'],
            ['hostname', 'example.com'],
            ['optional settings', 'y'],
            ['ssh key', 'special_key'],
            ['port', 3000],
            ['define a project', 'y'],
            ['project name', 'testproject'],
            ['project root', '/project/root'],
            ['scripts to the project', 'y'],
            ['script name', 'testscript'],
            ['script description', 'A Script'],
            ['script command', 'my script'],
            ['another script', 'n'],
            ['another project', 'n']
        ]);

        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    protected function getApplication($mersey)
    {
        $app = new Application();
        $app->add(new \Weeks\Mersey\Commands\AddServerCommand($mersey));

        return $app;
    }

    protected function getLocalMerseyMock($expectedServerConfig, $originalServerConfig = [])
    {
        $mersey = $this->getMerseyMock();
        $mersey
            ->shouldReceive('getServersConfig')
            ->once()
            ->andReturn('servers.json');
        $mersey
            ->shouldReceive('loadServerConfig')
            ->once()
            ->andReturn($originalServerConfig);
        $mersey
            ->shouldReceive('updateConfig')
            ->once()
            ->with('servers.json', $expectedServerConfig)
            ->andReturn(true);

        return $mersey;
    }
}