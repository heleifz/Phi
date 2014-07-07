<?php

namespace Phi;

class ArticleCollector {

	private $fileSystem;
	private $context;
	private $parsers;

	public function __construct(FileSystem $fileSystem,
								Context $context,
								ParserDispatcher $parsers) {
		$this->fileSystem = $fileSystem;
		$this->context = $context;
		$this->parsers = $parsers;
	}

	public function collect($path) {
		$sources = $this->getArticlePath($path);		
		$results = array();
		$defaults = $this->context->get('config.defaults');
		$defaults = $this->normalizeDefaults($defaults);
		$urlMap = array();
		foreach ($sources as $absolute => $pathinfo) {
			$current = $this->parsers->dispatch($absolute);
			// inject metadata computed at this level
			$current['dir'] = $pathinfo['relativeDir'];
			$default = $this->mergeDefaults($pathinfo['relative'], $defaults);
			$current = \Phi\Utils::arrayMergeRecursiveDistinct($default, $current);
			$current = $this->resolveVariables($current);	
			$id = trim($current['dir'].'/'.$this->fileSystem->fileName($absolute), '/\\');
			$url = \Phi\Utils::normalizeUrl($current['url']);
			// id is original article relative path (without extension)
			$current['id'] = $id;
			$current['relativeUrl'] = $url;
			$current['url'] = $this->context->get('site.root').$url;
			$urlMap[$id] = $url;
			$results[] = $current;
		}
		$this->context->merge(new \Phi\Context($results), 'site.articles');
		$this->context->merge(new \Phi\Context($urlMap), 'site.url_map');
	}

	private function resolveVariables($metadata) {
		// substitude variables
		foreach ($metadata as $k => $v) {
			// do not substitude content part
			if ($k != 'content' && is_string($v)) {
				$metadata[$k] = \Phi\Utils::insertVariables($v, $metadata);
			}
		}	
		return $metadata;
	}

	private function normalizeDefaults($defaults) {
		foreach ($defaults as &$d) {
			if (array_key_exists('pattern', $d)) {
				$d['pattern'] = strtr($d['pattern'], '\\', '/');
			}
		}	
		return $defaults;
	}

	private function mergeDefaults($relative, $defaults) {
		$result = array();
		foreach ($defaults as $default) {
			if (!array_key_exists('pattern', $default) ||
				!array_key_exists('meta', $default)) {
				throw new \Exception("Fail parsing configuration file : ".
					"cannot find 'pattern' and 'meta' field in defaults item.");
			}
			if (fnmatch($default['pattern'], strtr($relative, '\\', '/'))) {
				$result = \Phi\Utils::arrayMergeRecursiveDistinct($default['meta'], $result);
			}
		}	
		return $result;
	}

	private function getArticlePath($path) {
		return $this->fileSystem->walk($path, true /* ignore VCS */,
			$this->parsers->getExtensions(),
			$this->context->get('config.exclude_path') ? $this->context->get('config.exclude_path') : array(),
			$this->context->get('config.exclude_name') ? $this->context->get('config.exclude_name') : array());
	}
}