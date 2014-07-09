<?php

namespace Phi\GenerateService;

class ArticleReader {

	private $fileSystem;
	private $context;
	private $parsers;
	private $frontMatter;
	private $filenameParser;

	public function __construct(\Phi\FileSystem $fileSystem,
								\Phi\Context $context,
								\Phi\FilenameParser $filenameParser,
								\Phi\ParserDispatcher $parsers,
								\Phi\FrontMatter $frontMatter) {
		$this->fileSystem = $fileSystem;
		$this->context = $context;
		$this->parsers = $parsers;
		$this->frontMatter = $frontMatter;
		$this->filenameParser = $filenameParser;
	}

	public function read($pathinfo) {
		$abs = $pathinfo['absolute'];
		$defaults = $this->context->get('config.defaults');
		$defaults = $this->normalizeDefaults($defaults);
		$parser = $this->parsers->dispatch($abs);
		$text = $this->fileSystem->read($abs);
		$raw = $this->frontMatter->parse($text);
		$article = $raw['metadata'];
		$article['content'] = $parser->parse($raw['content']);
		$article['dir'] = $pathinfo['relativeDir'];
		$dateAndName = $this->filenameParser->parse($abs);
		$article = array_merge($article, $dateAndName);
		$default = $this->mergeDefaults($pathinfo['relative'], $defaults);
		$article = \Phi\Utils::arrayMergeRecursiveDistinct($default, $article);
		$article = $this->resolveVariables($article);	
		$id = trim($article['dir'].'/'.$this->fileSystem->fileName($pathinfo['absolute']), '/\\');
		$url = \Phi\Utils::normalizeUrl($article['url']);
		// id is original article relative path (without extension)
		$article['id'] = $id;
		$article['relativeUrl'] = $url;
		$article['url'] = $this->context->get('site.root').$url;
		return $article;
	}

	public function getExtensions() {
		return $this->parsers->getExtensions();
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
			if (!array_key_exists('pattern', $d) ||
				!array_key_exists('meta', $d)) {
				throw new \Exception("Fail parsing configuration file : ".
					"cannot find 'pattern' and 'meta' field in defaults item.");
			}
			$d['pattern'] = strtr($d['pattern'], '\\', '/');
		}	
		return $defaults;
	}

	private function mergeDefaults($relative, $defaults) {
		strstr($relative, '\\', '/');
		$matched = array_filter($defaults, function ($item) use($relative) {
			return fnmatch($item['pattern'], $relative);
		});
		return array_reduce($matched, function ($prev, $item) use($relative) {
			return \Phi\Utils::arrayMergeRecursiveDistinct($item['meta'], $prev);
		}, array());
	}

}