<?php

namespace Weeks\Mersey\Commands;

use \Mockery as m;
use Symfony\Component\Console\Application;
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
        $commandTester->execute(array('command' => $command->getName()));
        $this->assertRegExp('/Editing servers\.json/', $commandTester->getDisplay());
    }
}