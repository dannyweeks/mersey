<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;
use Weeks\Mersey\Server;

class EditServersCommandTest extends TestCase
{
    /**
     * @test
     */
    public function execute_opens_server_config()
    {
        $mersey = $this->getMerseyMock();

        $mersey->shouldReceive('getServersConfig')
            ->andReturn('server-config.json');

        $command = $this->getApplication($mersey)->find('edit');

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName()], ['verbosity' => Output::VERBOSITY_DEBUG]);

        $this->assertContains('open server-config.json', $tester->getDisplay());

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    private function getApplication($mersey)
    {
        $app = new Application();
        $app->add(new \Weeks\Mersey\Commands\EditServersCommand($mersey));

        return $app;
    }
}