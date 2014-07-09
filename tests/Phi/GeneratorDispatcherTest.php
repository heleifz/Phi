<?php

namespace Phi;

use \Mockery as m;

class Generator extends \PHPUnit_Framework_TestCase {

	public function testDispatch() {
		$d = new \Phi\GeneratorDispatcher;
		$gen1 = m::mock('Phi\\Generator');
		$gen2 = m::mock('Phi\\Generator');
		$gen1->shouldReceive('getName')->andReturn('gen1');
		$gen2->shouldReceive('getName')->andReturn('gen2');
		$d->register($gen1);
		$d->register($gen2);
		$this->assertEquals($gen1, $d->dispatch('gen1'));
		$this->assertEquals($gen2, $d->dispatch('gen2'));
	}

    /**
     * @expectedException \Exception
     */
	public function testIllegal() {
		$d = new \Phi\GeneratorDispatcher;
		$gen1 = m::mock('Phi\\Generator');
		$gen2 = m::mock('Phi\\Generator');
		$gen1->shouldReceive('getName')->andReturn('gen1');
		$gen2->shouldReceive('getName')->andReturn('gen2');
		$d->register($gen1);
		$d->register($gen2);
		$d->dispatch('gen3');
	}

}