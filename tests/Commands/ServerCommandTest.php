<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;
use Weeks\Mersey\Components\Server;

class ServerCommandTest extends TestCase
{
    /**
     * @test
     */
    public function execute_connects_to_a_basic_server()
    {
        $mersey = $this->getMerseyMock();
        $mersey->shouldReceive('getGlobalScripts')->andReturn([]);
        $mersey->shouldReceive('serverIsAccessible')->andReturn(true);

        $commandInstance = $this->createCommand($mersey, $this->getTestServer('basic'));

        $command = $this->getApplication($commandInstance)->find('testserver');

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName()], ['verbosity' => Output::VERBOSITY_DEBUG]);

        $this->assertContains('ssh -t -p 22 -i ~/.ssh/id_rsa testuser@example.com', $tester->getDisplay());

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    /**
     * @test
     */
    public function execute_connects_to_a_server_and_enters_project()
    {
        $mersey = $this->getMerseyMock();

        $mersey->shouldReceive('getGlobalScripts')->andReturn([]);
        $mersey->shouldReceive('serverIsAccessible')->andReturn(true);

        $commandInstance = $this->createCommand($mersey, $this->getTestServer('with-project'));

        $command = $this->getApplication($commandInstance)->find('testserver');

        $tester = new CommandTester($command);

        $tester->execute(
            [
                'command' => $command->getName(),
                'project' => 'testproject',
            ],
            ['verbosity' => Output::VERBOSITY_DEBUG]
        );

        $this->assertContains("ssh -t -p 22 -i ~/.ssh/id_rsa testuser@example.com 'cd /home/testserver/testproject && bash'",
            $tester->getDisplay());

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    /**
     * @test
     */
    public function execute_connects_to_a_server_and_runs_project_script()
    {
        $mersey = $this->getMerseyMock();

        $mersey->shouldReceive('getGlobalScripts')->andReturn([]);
        $mersey->shouldReceive('serverIsAccessible')->andReturn(true);

        $commandInstance = $this->createCommand($mersey, $this->getTestServer('with-script'));

        $command = $this->getApplication($commandInstance)->find('testserver');

        $tester = new CommandTester($command);

        $tester->execute(
            [
                'command' => $command->getName(),
                'project' => 'testproject',
                'script'  => 'testscript',
            ],
            ['verbosity' => Output::VERBOSITY_DEBUG]
        );

        $this->assertContains("cd /home/testserver/testproject; running script on server;", $tester->getDisplay());

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    /**
     * @test
     */
    public function it_tries_to_guess_the_project_name_if_input_is_not_a_real_project()
    {
        $mersey = $this->getMerseyMock();

        $mersey->shouldReceive('getGlobalScripts')->andReturn([]);
        $mersey->shouldReceive('serverIsAccessible')->andReturn(true);

        $commandInstance = $this->createCommand($mersey, $this->getTestServer('with-project'));

        $command = $this->getApplication($commandInstance)->find('testserver');

        $this->mockAnswersUsingArray($command, [
            ['did you mean', 'y']
        ]);

        $tester = new CommandTester($command);

        $tester->execute(
            [
                'command' => $command->getName(),
                'project' => 'testprojec',
            ],
            ['verbosity' => Output::VERBOSITY_DEBUG]
        );

        $this->assertContains("ssh -t -p 22 -i ~/.ssh/id_rsa testuser@example.com 'cd /home/testserver/testproject && bash'",
            $tester->getDisplay());
        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    private function createCommand($mersey, $config)
    {
        $command = new \Weeks\Mersey\Commands\ServerCommand($mersey, $config->name);

        $command->setServer(new TestServer($config, $mersey->getGlobalScripts()));

        return $command;
    }

    private function getApplication($command)
    {
        $app = new Application();

        $app->add($command);

        return $app;
    }

    private function getTestServer($name)
    {
        return json_decode(file_get_contents(loadFixture('server', $name . '.json')));
    }
}

class TestServer extends Server
{
    public function isAccessible()
    {
        return true;
    }
}