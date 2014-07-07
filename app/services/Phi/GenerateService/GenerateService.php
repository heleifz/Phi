<?php

namespace Phi\GenerateService;

class GenerateService implements \Phi\Service {

	private $console;
	private $context;
	private $parsers;
	private $renderer;
	private $fileSystem;
	private $pluginManager;

	private $path;
	private $baseContext;

	public function __construct(\Phi\PluginManager $pluginManager,
								\Phi\ParserDispatcher $parsers,
								\Phi\GeneratorDispatcher $generator,
								\Phi\FileSystem $fileSystem,
								\Phi\Console $console,
								\Phi\Context $context) {

		$this->pluginManager = $pluginManager;
		$this->context = $context;
		$this->console = $console;
		$this->parsers = $parsers;
		$this->generator = $generator;
		$this->fileSystem = $fileSystem;
	}

	public function getName() {
		return "generate";
	}

	public function getDescription() {
		return "Generate static site.";
	}

	public function execute($arguments, $flags) {

		$this->initialize($arguments[0]);
		$sources = $this->getArticlePath();		
		$results = array();
		$defaults = $this->context->get('config.defaults');
		// normalize backslashes in pattern
		foreach ($defaults as &$d) {
			if (array_key_exists('pattern', $d)) {
				$d['pattern'] = strtr($d['pattern'], '\\', '/');
			}
		}
		$urlMap = array();
		foreach ($sources as $absolute => $pathinfo) {
			$current = $this->parsers->dispatch($absolute);
			// inject metadata computed at this level
			$current['dir'] = $pathinfo['relativeDir'];
			foreach ($defaults as $default) {
				if (!array_key_exists('pattern', $default) ||
					!array_key_exists('meta', $default)) {
					throw new \Exception("Fail parsing configuration file : ".
						"cannot find 'pattern' and 'meta' field in defaults item.");
				}
				if (fnmatch($default['pattern'], strtr($pathinfo['relative'], '\\', '/'))) {
					$current = \Phi\Utils::arrayMergeRecursiveDistinct($default['meta'], $current);
				}
			}
			$current = $this->resolveVariables($current);	
			$id = trim($current['dir'].'/'.$this->fileSystem->fileName($absolute), '/\\');
			$url = \Phi\Utils::normalizeUrl($current['url']);
			// id is original article relative path (without extension)
			$current['id'] = $id;
			$current['relativeUrl'] = $url;
			$current['url'] = $this->context->get('root').$url;
			$urlMap[$id] = $url;
			$results[] = $current;
		}
		$this->baseContext['site']['articles'] = $results;
		$this->baseContext['site']['url_map'] = $urlMap;
		// generate site
		$generatorName = $this->context->get('config.generator');
		$generator = $this->generator->dispatch($generatorName);
		if (!$generator) {
			throw new \Exception("Could not find generator : $generatorName.");
		}
		$this->console->write('Generating pages...');
		$generator->generate($this->baseContext);
		$this->console->writeLine('done.');
		$this->console->writeLine('Success!');
	}

	public function getCommandOptions() {
		$path = new \Phi\CommandOption();
		$path->setDescription("Path to the Phi application directory.")
			 ->setDefault('.')->setRequired()
			 ->setValidator(array($this->fileSystem, 'isDirectory'),
			 	            "Invalid Phi application path");
		return array($path);
	}

	private function getArticlePath() {
		return $this->fileSystem->walk($this->path.'/'.$this->context->get('config.source'),
			true /* ignore VCS */,
			$this->parsers->getExtensions(),
			$this->context->get('config.exclude_path') ? $this->context->get('config.exclude_path') : array(),
			$this->context->get('config.exclude_name') ? $this->context->get('config.exclude_name') : array());
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

	private function initialize($path) {

		$this->path = $path;

		$this->console->write("Loading config file...");
		// load default configuration add merge it with application's
		$this->context->merge(\Phi\Context::fromYML($path.'/config.yaml'), 'config');
		$this->console->writeLine("done.");

		// set timezone
		date_default_timezone_set($this->context->get('config.timezone'));

		// clean up destination	
		$this->console->write("Cleaning up old site...");
		$this->fileSystem->clearDirectory($path.'/'.$this->context->get('config.destination'));
		$this->console->writeLine("done.");

		// copy assets to destination	
		$this->console->write("Copying assets...");
		$this->fileSystem->copyDirectory($path.'/'.$this->context->get('config.assets'),
										 $path.'/'.$this->context->get('config.destination'));
		$this->console->writeLine("done.");

		$this->console->write("Loading plugins...");
		$this->pluginManager->registerDirectory($path.'/'.$this->context->get('config.plugins'));
		$this->console->writeLine("done.");

		// initialize global context
		$this->baseContext = array(
			'site' => array(
				'name' => $this->context->get('config.name'),
				'encoding' => $this->context->get('config.encoding'),
				'root' => $this->context->get('config.root'),
				'project' => $this->path,
				'time' => date("Y-m-d H:i:s"),
				'config' => $this->context->get('config')
			)
		);
	}
}