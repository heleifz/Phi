<?php

namespace Phi;

use \Mockery as m;

class FilenameParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider filenameProvider
	 */
	public function testParse($abs, $expected) {
		$fs = m::mock('Phi\\FileSystem');
		$fs->shouldReceive('modificationTime')->andReturn(1404808327);
		$fs->shouldReceive('fileName')->passthru();
		$filenameParser = new \Phi\FilenameParser($fs);
		$result = $filenameParser->parse($abs);
		foreach ($expected as $k => $v) {
			$this->assertEquals($v, $result[$k]);
		}
	}

    public function filenameProvider() {
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