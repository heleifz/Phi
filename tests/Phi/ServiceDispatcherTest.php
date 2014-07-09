<?php

namespace Phi;

use \Mockery as m;

class ServiceDispatcherTest extends \PHPUnit_Framework_TestCase {

	private $conMock;

	public function setUp() {
		$this->conMock = m::mock('Phi\\Console');
		$this->conMock->written = false;
		$m = $this->conMock;
		$this->conMock->shouldReceive('write')->zeroOrMoreTimes()
			 ->andReturnUsing(function () use($m) {
		 		$m->written = true;
			});
	}

	public function testDispatch() {
		$service1 = $this->mockService('one');
		$service2 = $this->mockService('two');
		$d = new \Phi\ServiceDispatcher($this->conMock);
		$d->register($service1);
		$d->register($service2);
		$this->assertFalse($service1->called);
		$this->assertFalse($service2->called);
		$d->dispatch(array('phi.php', 'one'));
		$this->assertTrue($service1->called);
		$this->assertFalse($service2->called);
		$d->dispatch(array('phi.php', 'two'));
		$this->assertTrue($service2->called);
		$this->assertTrue($service1->called);
		$this->assertFalse($this->conMock->written);
	}

	public function testNotRegistered() {
		$service1 = $this->mockService('one');
		$service2 = $this->mockService('two');
		$d = new \Phi\ServiceDispatcher($this->conMock);
		$d->register($service1);
		$d->register($service2);
		$d->dispatch(array('phi.php', 'three'));
		$this->assertTrue($this->conMock->written);
	}

	private function mockService($name) {
		$service = m::mock('Phi\\Service');
		$service->shouldReceive('getName')->andReturn($name);
		$service->shouldReceive('getDescription')->andReturn($name.' descrip.');
		$service->shouldReceive('getCommandOptions')->andReturn(array());
		$service->called = false;
		$service->shouldReceive('execute')->andReturnUsing(function () use($service) {
			$service->called = true;
		});
		return $service;
	}

}