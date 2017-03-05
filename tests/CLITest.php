<?php

class CLITest extends TestCase
{
    /**
    * @test
    */
    public function it_runs_successfully()
    {
        $output = $this->executeCommand('./mersey -vvv');

        $this->assertExitCode(0, $output);
        $this->assertContains('Mersey version ' . MERSEY_VERSION, $output);
    }
}