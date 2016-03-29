<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;
use Mockery as m;

class EditScriptsCommandTest extends TestCase
{
    /**
     * @test
     */
    public function execute_opens_server_config()
    {
        $mersey = $this->getMerseyMock();

        $mersey->shouldReceive('getScriptsConfig')
            ->andReturn('scripts-config.json');

        $command = $this->getApplication($mersey)->find('scripts');

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName()], ['verbosity' => Output::VERBOSITY_DEBUG]);

        $this->assertContains('open scripts-config.json', $tester->getDisplay());

        $this->assertSame(0, $tester->getStatusCode(), 'Exit code is showing an error');
    }

    private function getApplication($mersey)
    {
        $app = new Application();
        $app->add(new \Weeks\Mersey\Commands\EditScriptsCommand($mersey));

        return $app;
    }
}