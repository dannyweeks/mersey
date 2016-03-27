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
     * Execute a live command and return the output
     *
     * @param $command
     * @return string
     */
    protected function executeCommand($command)
    {
        ob_start();
        passthru($command, $exitCode);
        echo "\nTest Exit Code: " . $exitCode;

        return ob_get_clean();
    }

    /**
     * @param string $command
     * @param string $output
     */
    protected function assertCommandExecuted($command, $output)
    {
        $this->assertRegExp('/Executing command \'' . $command . '\'/i', $output);
    }

    /**
     * @param $output
     * @param $code
     */
    protected function assertExitCode($code, $output)
    {
        $this->assertRegExp('/Test Exit Code: ' . $code . '/i', $output);
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    protected function getMerseyMock()
    {
        $mersey = m::mock(Mersey::class);

        return $mersey;
    }
}

