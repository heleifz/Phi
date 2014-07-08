<?php

use \Mockery as m;

class YAMLReaderTest extends PHPUnit_Framework_TestCase {

	private $fs;

	public function setUp() {
    	$this->fs = m::mock('Phi\\FileSystem');
    	$this->fs->shouldReceive('read')
    		 ->andReturn("---\nurl:hello---main body");
    	$this->fs->shouldReceive('fileName')->passthru();
    	$this->fs->shouldReceive('modificationTime')->andReturn(1404808327);
	}

	public function testMainBody() {
    	$reader = new \Phi\YAMLReader\YAMLReader($this->fs);
    	$reader->load('some name');
    	$body = $reader->getBody();
    	$this->assertEquals("main body", $body);
	}

	/**
	 * @dataProvider metaProvider
	 */
    public function testMetadata($name, $expected) {
    	$reader = new \Phi\YAMLReader\YAMLReader($this->fs);
    	$reader->load($name);
    	$meta = $reader->getMetadata();
    	$this->assertEquals('hello', $meta['url']);
    	foreach ($expected as $key => $value) {
	    	$this->assertEquals($value, $meta[$key]);
    	}
    }

    public function metaProvider() {
    	return array(
    		array('2012-5-6-title.md', array('year' => '2012', 'month' => '05', 'day' => '06')),
    		array('2012-05-6-title.md', array('year' => '2012', 'month' => '05', 'day' => '06')),
    		array('2012-5-06-title.md', array('year' => '2012', 'month' => '05', 'day' => '06')),
    		array('dir\\dir/dd\\ddd/2012-05-06-title.md', array('year' => '2012', 'month' => '05', 'day' => '06')),
    		array('2013-11-30-title.md', array('year' => '2013', 'month' => '11', 'day' => '30')),
    		array('title.md', array('year' => '2014', 'month' => '07', 'day' => '08')),
    		array('2012-5-6-title.md', array('short_year' => '12', 'short_month' => '5', 'short_day' => '6')),
    		array('2012-05-6-title.md', array('short_year' => '12', 'short_month' => '5', 'short_day' => '6')),
    		array('dir/2012-5-06-title.md', array('short_year' => '12', 'short_month' => '5', 'short_day' => '6')),
    		array('f/f2/2012-05-06-title.md', array('short_year' => '12', 'short_month' => '5', 'short_day' => '6')),
    		array('dir/dir/dir/2013-11-30-title.md', array('short_year' => '13', 'short_month' => '11', 'short_day' => '30')),
    		array('title.md', array('short_year' => '14', 'short_month' => '7', 'short_day' => '8')),
			array('2012-5-6-title.md', array('date' => '2012/05/06', 'short_date' => '12/5/6')),
			array('2012-05-6-title.md', array('date' => '2012/05/06', 'short_date' => '12/5/6')),
			array('/2012-5-06-title.md', array('date' => '2012/05/06', 'short_date' => '12/5/6')),
			array("\\2012-05-06-title.md", array('date' => '2012/05/06', 'short_date' => '12/5/6')),
			array('//2013-11-30-title.md', array('date' => '2013/11/30', 'short_date' => '13/11/30')),
			array('title.md', array('date' => '2014/07/08', 'short_date' => '14/7/8')),
    	);
    }
}


