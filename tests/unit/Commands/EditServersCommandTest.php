<?php

namespace Weeks\Mersey\Commands;

use \Mockery as m;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class EditServerCommandTest extends \TestCase
{
    /**
    * @test
    */
    public function it_executes_the_command()
    {
        $application = new Application();
        $application->add(new EditServersCommand());

        $command = $application->find('edit');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ], ['verbosity' => OutputInterface::VERBOSITY_DEBUG]);

        $this->assertRegExp('/Editing servers\.json/', $commandTester->getDisplay());
        $this->assertRegExp('/open servers\.json/', $commandTester->getDisplay());
    }
}