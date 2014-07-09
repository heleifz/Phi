<?php

namespace Phi;

use \Mockery as m;

class PluginManagerTest extends \PHPUnit_Framework_TestCase {

	private $appMock;
	private $fsMock;

	public function setUp() {
		$this->appMock = m::mock('Phi\\Application');
		$this->fsMock = m::mock('Phi\\FileSystem');
	}

	public function testRegister() {
		$manager = new \Phi\PluginManager($this->appMock, $this->fsMock);
		$this->appMock->shouldReceive('registerParser')->with('TestParser');
		$manager->register('TestParser');
		$this->appMock->shouldReceive('registerGenerator')->with('TestGenerator');
		$manager->register('TestGenerator');
	}	

	public function testRegisterDirectories() {
		$this->fsMock->shouldReceive('fileName')->passthru();
		$this->fsMock->shouldReceive('includeOnce')->times(3);
		$this->fsMock->shouldReceive('walk')->andReturn(
			array(
				array('absolute' => 'dir/SomeParser.php'),	
				array('absolute' => 'dir/SomeGenerator.php'),	
			)
		);
		$this->appMock->shouldReceive('registerParser')->with('SomeParser');
		$this->appMock->shouldReceive('registerGenerator')->with('SomeGenerator');
		$manager = new \Phi\PluginManager($this->appMock, $this->fsMock);
		$manager->registerDirectory('somedir');
	}

	/**
     * @expectedException \Exception
     */
	public function testIllegalPluginName() {
		$manager = new \Phi\PluginManager($this->appMock, $this->fsMock);
		$this->appMock->shouldReceive('registerParser');
		$this->appMock->shouldReceive('registerGenerator');
		$manager->register('TestParsera');
	}
	
}