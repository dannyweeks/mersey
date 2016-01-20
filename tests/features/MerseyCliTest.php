<?php

class MerseyCliTest extends \TestCase
{
    /**
    * @test
    */
    public function app_is_instantiated()
    {
        $output = $this->executeCommand('./mersey');

        $this->assertContains('Connect to Test Server', $output);
        $this->assertContains('Mersey version', $output);
    }
}