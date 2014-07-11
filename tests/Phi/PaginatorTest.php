<?php

namespace Phi;

class PaginatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Paginator is not configured.
	 */
	public function testNoPaginator() {
		$article = array('title' => 'test');
		$p = new Paginator($article, array());
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Page URL for paginator is not configured.
	 */
	public function testNoPageUrl() {
		$article = array('title' => 'test', 'paginator' => array('per_page' => 3));
		$p = new Paginator($article, array());
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Article per page for paginator is not configured.
	 */
	public function testNoPerPage() {
		$article = array('title' => 'test', 'paginator' => array('page_url' => 'foobar:num.html'));
		$p = new Paginator($article, array());
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Article per page must be integer number.
	 */
	public function testIllegalPerPage() {
		$article = array('title' => 'test', 'paginator' => array(
			'per_page' => '123bc',
			'page_url' => 'foobar:num.html')
		);
		$p = new Paginator($article, array());
	}
}