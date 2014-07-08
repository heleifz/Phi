<?php

use \Mockery as m;

class ParserDispatcherTest extends PHPUnit_Framework_TestCase {

	private $fsMock;
	private $rdMock;

	public function setUp() {
		$this->fsMock = m::mock('Phi\\FileSystem');
		$this->rdMock = m::mock('Phi\\Reader');
	}

	public function testRegister() {
		$d = new \Phi\ParserDispatcher($this->fsMock, $this->rdMock);
		$parser = m::mock('Phi\\Parser');
		$parser->shouldReceive('getExtensions')->times(2)
			   ->andReturn(array('md', 'mdown'), array('html', 'md'));
		$d->register($parser);
		$this->assertEquals(array('md', 'mdown'), $d->getExtensions());
		$d->register($parser);
		$this->assertEquals(array('md', 'mdown', 'html'), $d->getExtensions());
	}

	public function testDispatch() {
		$parser = m::mock('Phi\\Parser');
		$parser->shouldReceive('getExtensions')->times(2)
			   ->andReturn(array('md', 'mdown'), array('html', 'md'));
		$d = new \Phi\ParserDispatcher($this->fsMock, $this->rdMock);
		$d->register($parser);
		$d->register($parser);
		$parser->shouldReceive('parse')->times(2)
			   ->andReturn('hello', 'world');
		$this->fsMock->shouldReceive('getExtension')
			 ->times(2)->andReturn('md', 'html');
		$this->rdMock->shouldReceive('load')
			 ->times(2);
		$this->rdMock->shouldReceive('getBody')
			 ->times(2)->andReturn('hellobody', 'worldbody');
		$this->rdMock->shouldReceive('getMetadata')
			 ->times(2)->andReturn(array('title'=>'nihao'), array('url'=>'shijie'));
		$this->assertEquals(array('title'=>'nihao', 'content'=>'hello'), $d->dispatch('one.md'));
		$this->assertEquals(array('url'=>'shijie', 'content'=>'world'), $d->dispatch('two.html'));
	}

}