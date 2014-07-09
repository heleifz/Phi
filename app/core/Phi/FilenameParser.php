<?php

namespace Phi;

class FilenameParser {

	private $fileSystem;

	public function __construct(\Phi\FileSystem $fileSystem) {
		$this->fileSystem = $fileSystem;
	}

	public function parse($absolute) {
		$absolute = str_replace('\\', '/', $absolute);
		$filename = $this->fileSystem->fileName($absolute);
		$fields = explode('-', $filename, 4);
		$result = array();
		if (count($fields) != 4 || !checkdate($fields[1], $fields[2], $fields[0])) {
			$result['name'] = $filename;
			$timestamp = $this->fileSystem->modificationTime($absolute);
		} else {
			// if filename is not according to year-month-day-name
			// then use the fullname as "name", and file mtime as timestamp
			$result["name"] = $fields[3];
			$timestamp = strtotime($fields[0].'/'.$fields[1].'/'.$fields[2]);
		}
		// normalize date format		
		$longDate = date("Y/m/d", $timestamp);
		$shortDate = date("y/n/j", $timestamp);
		$longFields = explode('/', $longDate);
		$shortFields = explode('/', $shortDate);
		$result["timestamp"] = $timestamp;
		$result["year"] = $longFields[0];
		$result["month"] = $longFields[1];
		$result["day"] = $longFields[2];
		$result["short_year"] = $shortFields[0];
		$result["short_month"] = $shortFields[1];
		$result["short_day"] = $shortFields[2];
		$result["date"] = $longDate;
		$result["short_date"] = $shortDate;
		return $result;
	}

}