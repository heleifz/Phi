<?php

namespace Phi\TwigGenerator;

class PhiExtension extends \Twig_Extension {

	private $context;

	public function __construct($context) {
		$this->context = $context;
	}

	public function getName() {
		return 'phi';
	}

	public function getFilters() {
		return array(
			new \Twig_SimpleFilter('excerpt', array($this, 'excerptFilter')),
			new \Twig_SimpleFilter('truncate', array($this, 'truncateFilter')),
			new \Twig_SimpleFilter('where_*', array($this, 'whereFilter')),
			new \Twig_SimpleFilter('group_by_*', array($this, 'groupByFilter')),
			new \Twig_SimpleFilter('sort_by_*_*', array($this, 'stableSortFilter')),
		);
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('url', array($this, 'generateArticleUrl')),
		);
	}

	public function generateArticleUrl($name) {
		$urlMap = $this->context['site']['url_map'];
		if (array_key_exists($name, $urlMap)) {
			return $urlMap[$name];
		} else {
			return "";
		}
	}

	public function excerptFilter($html, $tag='p') {
		return \Phi\Utils::excerpt($html, $tag);
	}

	public function truncateFilter($text, $maxChar) {
		if (!function_exists("mb_detect_encoding")) {
			return substr($text, 0, $maxChar);
		} else {
			$charset = mb_detect_encoding($text, 
				array_merge(array("GB2312"), mb_detect_order()));
			if ($charset === false) {
				$charset = "UTF-8";
			}
			return mb_substr($text, 0, $maxChar, $charset);
		}
	}

	public function groupByFilter($field, $arr) {
		$result = array();
		foreach ($arr as $ele) {
			$result[$ele[$field]][] = $ele;
		}
		return $result;
	}

	public function whereFilter($field, $arr, $comp, $val) {
		$result = array();
		// $comp = < <= >= >
		switch ($comp) {
			case '=':
				foreach ($arr as $ele) {
					if ($ele[$field] == $val) {
						$result[] = $ele;
					}
				}
				break;
			case '<':
				foreach ($arr as $ele) {
					if ($ele[$field] < $val) {
						$result[] = $ele;
					}
				}
				break;
			case '<=':
				foreach ($arr as $ele) {
					if ($ele[$field] <= $val) {
						$result[] = $ele;
					}
				}
				break;
			case '>':
				foreach ($arr as $ele) {
					if ($ele[$field] > $val) {
						$result[] = $ele;
					}
				}
				break;
			case '>=':
				foreach ($arr as $ele) {
					if ($ele[$field] >= $val) {
						$result[] = $ele;
					}
				}
				break;
			default:
				throw new \Twig_Error_Syntax("Illegal comparition operator : '$comp'");
				break;
		}
		return $result;
	}

	public function stableSortFilter($field, $method, $arr) {
		$a = $arr;
		if ($field == 'date') {
			$this->mergesort($a, 'timestamp');
		} else {
			$this->mergesort($a, $field);
		}
		if ($method == 'desc') {
			return array_reverse($a);
		} else {
			return $a;
		}
	}

	private function mergesort(&$array, $field) {
		// Arrays of size < 2 require no action.
		if (count($array) < 2) return;
		// Split the array in half
		$halfway = count($array) / 2;
		$array1 = array_slice($array, 0, $halfway);
		$array2 = array_slice($array, $halfway);
		// Recurse to sort the two halves
		$this->mergesort($array1, $field);
		$this->mergesort($array2, $field);
		// If all of $array1 is <= all of $array2, just append them.
		if (end($array1)[$field] <= $array2[0][$field]) {
			$array = array_merge($array1, $array2);
			return;
		}
		// Merge the two sorted arrays into a single sorted array
		$array = array();
		$ptr1 = $ptr2 = 0;
		while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
			if ($array1[$ptr1][$field] <= $array2[$ptr2][$field]) {
				$array[] = $array1[$ptr1++];
			}
			else {
				$array[] = $array2[$ptr2++];
			}
		}
		// Merge the remainder
		while ($ptr1 < count($array1)) $array[] = $array1[$ptr1++];
		while ($ptr2 < count($array2)) $array[] = $array2[$ptr2++];
		return;
	}
}	