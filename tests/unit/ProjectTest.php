<?php

namespace Weeks\Mersey;

class ProjectTest extends \TestCase {

    /**
     * @var Project
    */
    protected $project;

    protected function setUp()
    {
        $this->project = new Project('portfolio', '/var/www/portfolio', [
            'clean' => 'cd /; ls',
            'logs' => 'cat log.txt',
        ]);
    }

    /**
     * @test
     */
    public function it_returns_a_full_root_command()
    {
        $this->assertEquals('cd /var/www/portfolio && bash', $this->project->getRootCommand());
    }
    
    /**
    * @test
    */
    public function it_returns_the_available_scripts()
    {
        $scripts = $this->project->availableScripts();

        $this->assertEquals(['clean', 'logs'], $scripts);
    }

    /**
    * @test
    */
    public function it_has_the_additional_commands_constant()
    {
        $this->assertEquals("; read -p \"Remote script completed. Press enter to continue..\"; exit", Project::SCRIPT_COMMAND);
    }

    /**
    * @test
    */
    public function it_returns_the_fully_qualified_script_command()
    {
        $command = $this->project->getScript('clean');

        $this->assertEquals("cd /; ls; read -p \"Remote script completed. Press enter to continue..\"; exit", $command);
    }

    /**
    * @test
    */
    public function it_checks_if_a_script_exists()
    {
        $this->assertFalse($this->project->hasScript('apple'));
        $this->assertTrue($this->project->hasScript('clean'));
    }
}
