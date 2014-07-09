<?php

namespace Phi;

use \Mockery as m;

class ParserDispatcherTest extends \PHPUnit_Framework_TestCase {

	private $fsMock;

	public function setUp() {
		$this->fsMock = m::mock('Phi\\FileSystem');
	}

	public function testRegister() {
		$d = new \Phi\ParserDispatcher($this->fsMock);
		$parser = m::mock('Phi\\Parser');
		$parser->shouldReceive('getExtensions')->times(2)
			   ->andReturn(array('md', 'mdown'), array('html', 'md'));
		$d->register($parser);
		$this->assertEquals(array('md', 'mdown'), $d->getExtensions());
		$d->register($parser);
		$this->assertEquals(array('md', 'mdown', 'html'), $d->getExtensions());
	}

	public function testDispatch() {
		$parser1 = m::mock('Phi\\Parser');
		$parser1->shouldReceive('getExtensions')->once()
			    ->andReturn(array('md', 'mdown'));
		$parser2 = m::mock('Phi\\Parser');
		$parser2->shouldReceive('getExtensions')->once()
			    ->andReturn(array('htm', 'html'));
		$d = new \Phi\ParserDispatcher($this->fsMock);
		$d->register($parser1);
		$d->register($parser2);
		$this->fsMock->shouldReceive('getExtension')
			 ->times(2)->andReturn('md', 'html');
		$this->assertEquals($parser1, $d->dispatch('hello.md'));
		$this->assertEquals($parser2, $d->dispatch('hello.html'));
	}

	/**
     * @expectedException \Exception
     */
	public function testIllegal() {
		$parser1 = m::mock('Phi\\Parser');
		$parser1->shouldReceive('getExtensions')->once()
			    ->andReturn(array('md', 'mdown'));
		$parser2 = m::mock('Phi\\Parser');
		$parser2->shouldReceive('getExtensions')->once()
			    ->andReturn(array('htm', 'html'));
		$d = new \Phi\ParserDispatcher($this->fsMock);
		$d->register($parser1);
		$d->register($parser2);
		$this->fsMock->shouldReceive('getExtension')
			 ->times(2)->andReturn('css');
		$d->dispatch('hello.css');
	}

}