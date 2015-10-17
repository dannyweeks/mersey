<?php

namespace Weeks\Mersey;

use \Mockery as m;
use Symfony\Component\Console\Application;
use Weeks\Mersey\Factories\ProjectFactory;
use Weeks\Mersey\Factories\ServerFactory;
use Weeks\Mersey\Services\JsonValidator;

class MerseyTest extends \TestCase
{

    /**
    * @test
    */
    public function it_loads_valid_server_data()
    {
        // Given
        $consoleMock = m::mock(Application::class);
        $validatorMock = m::mock(JsonValidator::class);

        $validatorMock->shouldReceive('validate')->andReturn(true);

        $serverFactoryMock = m::mock(ServerFactory::class);
        $projectFactoryMock = m::mock(ProjectFactory::class);

        $mersey = $this->getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);

        // when

        $mersey->loadServersFromJson(testData('valid.json'));

        // then

        $this->assertEquals(json_decode(file_get_contents(testData('valid.json'))), $this->readAttribute($mersey, 'loadedServers'));

    }

    /**
     * @test
     * @expectedException \Weeks\Mersey\Exceptions\InvalidServerConfigException
     */
    public function it_throws_an_exception_when_invalid_server_data_is_provided()
    {
        // given
        $consoleMock = m::mock(Application::class);
        $validatorMock = m::mock(JsonValidator::class);

        $validatorMock->shouldReceive('validate')->andReturn(false);
        $validatorMock->shouldReceive('getErrors')->andReturn([
            [
                'property' => 'name',
                'message' => 'name is required',
            ]
        ]);

        $serverFactoryMock = m::mock(ServerFactory::class);
        $projectFactoryMock = m::mock(ProjectFactory::class);

        $mersey = $this->getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);

        // when
        $mersey->loadServersFromJson(testData('invalid.json'));


    }

    /**
    * @test
    * @expectedException \Weeks\Mersey\Exceptions\IllegalServerNameException
    */
    public function it_throws_exception_if_a_reserved_command_word_is_used()
    {
        // given
        $consoleMock = m::mock(Application::class);
        $validatorMock = m::mock(JsonValidator::class);

        $validatorMock->shouldReceive('validate')->andReturn(true);

        $serverFactoryMock = m::mock(ServerFactory::class);
        $projectFactoryMock = m::mock(ProjectFactory::class);

        $mersey = $this->getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);

        // when
        $mersey->loadServersFromJson(testData('illegalName.json'));

    }

    /**
    * @test
    */
    public function it_runs_the_app()
    {

        $consoleMock = m::mock(Application::class);
        $validatorMock = m::mock(JsonValidator::class);
        $serverFactoryMock = m::mock(ServerFactory::class);
        $projectFactoryMock = m::mock(ProjectFactory::class);
        $serverMock = m::mock(Server::class);

        $projectFactoryMock->shouldReceive('create')
            ->atLeast('once')
            ->andReturn(m::mock(Project::class));

        $validatorMock->shouldReceive('validate')->andReturn(true);
        $serverFactoryMock->shouldReceive('create')
            ->once()
            ->andReturn($serverMock);

        $serverMock->shouldReceive('addProject')
            ->atLeast('once');


        $consoleMock->shouldReceive('run')->once();

        $mersey = $this->getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);
        $mersey->loadServersFromJson(testData('valid.json'));
        $mersey->run();


    }
}