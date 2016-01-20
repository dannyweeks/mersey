<?php

use \Mockery as m;
use Weeks\Mersey\Mersey;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        m::close();
    }

    /**
     * @param $consoleMock
     * @param $validatorMock
     * @param $serverFactoryMock
     * @param $projectFactoryMock
     * @return Mersey
     */
    protected function getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock)
    {
        $consoleMock->shouldReceive('add')->atLeast()->once();

        return new Mersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);
    }

    /**
     * Execute a live command and return the output
     *
     * @param $command
     * @return string
     */
    protected function executeCommand($command)
    {
        ob_start();
        passthru($command);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * @param string $command
     * @param string $output
     */
    protected function assertCommandExecuted($command, $output)
    {
        return $this->assertRegExp('/Executing command \'' . $command . '\'/i', $output);
    }

}

