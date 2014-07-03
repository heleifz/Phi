<?php

namespace Phi\GenerateService;

class GenerateService implements \Phi\Service {

	private $app;
	private $util;	
	private $console;
	private $parsers;
	private $renderer;
	private $fileSystem;

	private $path;
	private $baseContext;

	public function __construct(\Phi\Application $app,
								\Phi\ParserDispatcher $parsers,
								\Phi\GeneratorDispatcher $generator,
								\Phi\FileSystem $fileSystem,
								\Phi\Console $console,
								\Phi\Config $config,
								\Phi\Utils $util) {
		$this->app = $app;
		$this->util = $util;
		$this->config = $config;
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
		// copy files, load configurations, set up context
		$this->initialize($arguments[0]);
		// get article path
		$sources = $this->getArticlePath();		
		// generate meta data for the site
		$results = array();
		$defaults = $this->config->get('defaults');
		$urlMap = array();
		foreach ($sources as $absolute => $pathinfo) {
			$current = $this->parsers->dispatch($absolute);
			// inject metadata computed at this level
			$current['dir'] = $pathinfo['relativeDir'];
			foreach ($defaults as $default) {
				if (!array_key_exists('pattern', $default) ||
					!array_key_exists('meta', $default)) {
					throw new \Exception("Fail parsing configuration file : ".
						"cannot find 'pattern' or 'meta' field in defaults item.");
				}
				if (fnmatch($default['pattern'], $pathinfo['relative'])) {
					$current = $this->util->arrayMergeRecursiveDistinct($default['meta'], $current);
				}
			}
			$current = $this->resolveVariables($current);	
			$id = trim($current['dir'].'/'.$this->fileSystem->fileName($absolute), '/\\');
			$url = $this->util->normalizeUrl($current['url']);
			$current['id'] = $id;
			$current['url'] = $url;
			$urlMap[$id] = $url;
			$results[] = $current;
		}
		$this->baseContext['site']['articles'] = $results;
		$this->baseContext['site']['url_map'] = $urlMap;
		// generate site
		$generatorName = $this->config->get('generator');
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
		return $this->fileSystem->walk($this->path.'/'.$this->config->get('source'),
			true /* ignore VCS */,
			$this->parsers->getExtensions(),
			$this->config->get('exclude_path') ? $this->config->get('exclude_path') : array(),
			$this->config->get('exclude_name') ? $this->config->get('exclude_name') : array());
	}

	private function resolveVariables($metadata) {
		// substitude variables
		foreach ($metadata as $k => $v) {
			// do not substitude content part
			if ($k != 'content' && is_string($v)) {
				$metadata[$k] = $this->util->insertVariables($v, $metadata);
			}
		}	
		return $metadata;
	}

	private function registerPlugins($path) {
		// scan only first level (users can put their library in sub directory)
		$files = $this->fileSystem->walk($path, true,
			array('php'), array(), array(), '< 1'); 
		foreach ($files as $file => $pathinfo) {
			$this->fileSystem->includeOnce($file);
			$className = $this->fileSystem->fileName($file);
			if (preg_match('/.*(?:p|P)arser$/', $className)) {
				$this->app->registerParser($className);
			} elseif (preg_match('/.*(?:g|G)enerator$/', $className)) {
				$this->app->registerGenerator($className);
			} else {
				$this->console->writeLine("Unknown plugin : $className.");
			}
		}
	}

	private function initialize($path) {

		$this->path = $path;

		$this->console->write("Loading config file...");
		// load default configuration add merge it with application's
		$this->config->setPath(__DIR__.'/../../../default.yaml');
		$this->config->mergePath($path.'/config.yaml');
		$this->console->writeLine("done.");

		// set timezone
		date_default_timezone_set($this->config->get('timezone'));

		// clean up destination	
		$this->console->write("Cleaning up old site...");
		$this->fileSystem->clearDirectory($path.'/'.$this->config->get('destination'));
		$this->console->writeLine("done.");

		// copy assets to destination	
		$this->console->write("Copying assets...");
		$this->fileSystem->copyDirectory($path.'/'.$this->config->get('assets'),
										 $path.'/'.$this->config->get('destination'));
		$this->console->writeLine("done.");

		$this->console->write("Loading plugins...");
		$this->registerPlugins($path.'/'.$this->config->get('plugins'));
		$this->console->writeLine("done.");

		// initialize global context
		$this->baseContext = array(
			'site' => array(
				'name' => $this->config->get('name'),
				'project' => $this->path,
				'time' => date("Y-m-d H:i:s"),
				'config' => $this->config->toArray()
			)
		);
	}
}