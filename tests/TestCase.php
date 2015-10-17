<?php
use \Mockery as m;
use Weeks\Mersey\Mersey;

abstract class TestCase extends \PHPUnit_Framework_TestCase{

    public function tearDown() {
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

}