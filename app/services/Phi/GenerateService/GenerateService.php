<?php

namespace Phi\GenerateService;

class GenerateService implements \Phi\Service {

	private $util;	
	private $console;
	private $parsers;
	private $renderer;
	private $fileSystem;

	private $path;
	private $baseContext;

	public function __construct(\Phi\ParserDispatcher $parsers,
								\Phi\FileSystem $fileSystem,
								\Phi\Console $console,
								\Phi\Config $config,
								\Phi\Utils $util,
								\Phi\Renderer $renderer) {
		$this->util = $util;
		$this->config = $config;
		$this->console = $console;
		$this->parsers = $parsers;
		$this->renderer = $renderer;
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

		// generate (partial) metadata for each article
		$results = array();
		$defaults = $this->config->get('defaults');
		foreach ($sources as $absolute => $pathinfo) {
			$current = $this->parsers->dispatch($absolute);
			// inject metadata computed at this level
			$current['dir'] = $pathinfo['relativeDir'];
			foreach ($defaults as $default) {
				if (fnmatch($default['pattern'], $pathinfo['relative'])) {
					$current = $this->util->arrayMergeRecursiveDistinct($default['meta'], $current);
					break;
				}
			}
			$current = $this->resolveVariables($current);	
			$current['url'] = $this->util->normalizeUrl($current['url']);
			$results[] = $current;
		}

		// render html file (fill in the rest metadata)
		$total = count($results);
		$this->baseContext['site']['articles'] = $results;
		for ($i = 0; $i < $total; $i++) {
			$current = $results[$i];
			$current['previous_article'] = $i > 0 ? $results[$i - 1] : NULL;
			$current['next_article'] = $i < ($total - 1) ? $results[$i + 1] : NULL;
			$this->baseContext['page'] = $current; 
			$page = $this->renderer->render($this->baseContext, $current['template']);
			$this->fileSystem->writeRecursively($this->path.'/'.
				$this->config->get('destination').'/'.$current['url'], $page); 
		}
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

	private function initialize($path) {

		$this->path = $path;

		$this->console->write("Loading config file...");
		// load default configuration add merge it with application's
		$this->config->setPath(__DIR__.'/../../../default.yaml');
		$this->config->mergePath($path.'/config.yaml');
		$this->console->writeLine("done.");

		// set timezone
		date_default_timezone_set($this->config->get('timezone'));

		// initialize template engine
		$this->console->write("Initializing renderer...");
		$this->renderer->setTemplatePath($path.'/'.$this->config->get('templates'));
		$this->console->writeLine("done.");

		// clean up destination	
		$this->console->write("Cleaning up old site...");
		$this->fileSystem->clearDirectory($path.'/'.$this->config->get('destination'));
		$this->console->writeLine("done.");

		// copy assets to destination	
		$this->console->write("Copying assets...");
		$this->fileSystem->copyDirectory($path.'/'.$this->config->get('assets'),
										 $path.'/'.$this->config->get('destination'));
		$this->console->writeLine("done.");

		// initialize global context
		$this->baseContext = array(
			'site' => array(
				'name' => $this->config->get('name'),
				'time' => date("Y-m-d H:i:s"),
				'config' => $this->config->toArray()
			)
		);
	}
}