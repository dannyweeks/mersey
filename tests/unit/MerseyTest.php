<?php

namespace Weeks\Mersey;

use \Mockery as m;
use Symfony\Component\Console\Application;
use Weeks\Mersey\Factories\ProjectFactory;
use Weeks\Mersey\Factories\ServerFactory;
use Weeks\Mersey\Server;
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

        $serverJson = $this->getValidServerJson();

        $servers = $this->createTempFile($serverJson);

        $validatorMock->shouldReceive('validate')->andReturn(true);

        $serverFactoryMock = m::mock(ServerFactory::class);
        $projectFactoryMock = m::mock(ProjectFactory::class);

        $mersey = $this->getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);

        // when

        $mersey->loadServersFromJson($servers);

        // then

        $this->assertEquals(json_decode($serverJson), $this->readAttribute($mersey, 'loadedServers'));

        unlink($servers);
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

        $invalidJson = '
        [
            {
                "displayName": "Personal Server",
                "username": "danny",
                "hostname": "192.168.0.1"
            }
        ]';

        $invalid = $this->createTempFile($invalidJson);

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
        $mersey->loadServersFromJson($invalid);

        unlink($invalid);

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

        $invalidJson = '
        [
            {
                "name": "help",
                "displayName": "Personal Server",
                "username": "danny",
                "hostname": "192.168.0.1"
            }
        ]';

        $invalid = $this->createTempFile($invalidJson);

        $validatorMock->shouldReceive('validate')->andReturn(true);

        $serverFactoryMock = m::mock(ServerFactory::class);
        $projectFactoryMock = m::mock(ProjectFactory::class);

        $mersey = $this->getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);

        // when
        $mersey->loadServersFromJson($invalid);

        unlink($invalid);
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


        $consoleMock->shouldReceive('run');

        $mersey = $this->getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);
        $mersey->loadServersFromJson($this->createTempFile($this->getValidServerJson()));
        $mersey->run();


    }

    /**
     * @param $consoleMock
     * @param $validatorMock
     * @param $serverFactoryMock
     * @param $projectFactoryMock
     * @return Mersey
     */
    private function getMersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock)
    {
        $consoleMock->shouldReceive('add')->atLeast()->once();

        return new Mersey($consoleMock, $validatorMock, $serverFactoryMock, $projectFactoryMock);
    }

    /**
     * @param $data
     * @return string
     */
    private function createTempFile($data)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'FOOBAR');
        $handle = fopen($filePath, "w");
        fwrite($handle, $data);
        fclose($handle);

        return $filePath;
    }

    private function getValidServerJson(){
        return '
        [
            {
                "name": "name",
                "displayName": "Personal Server",
                "username": "danny",
                "hostname": "192.168.0.1",
                "projects": [
                    {
                        "name": "project",
                        "root": "/var/www/project",
                        "scripts": {
                            "clean": "/dev/null > /var/www/project/today.log"
                        }
                    }
                ]
            }
        ]';
    }

}