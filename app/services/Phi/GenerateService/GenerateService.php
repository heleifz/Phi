<?php

namespace Phi\GenerateService;

class GenerateService implements \Phi\Service {

	private $finder;
	private $console;
	private $renderer;
	private $dispatcher;
	private $fileSystem;

	private $path = NULL;

	public function __construct(\Symfony\Component\Finder\Finder $finder,
								\Phi\ParserDispatcher $dispatcher,
								\Phi\FileSystem $fileSystem,
								\Phi\Console $console,
								\Phi\Config $config,
								\Phi\Renderer $renderer) {

		$this->finder = $finder;
		$this->config = $config;
		$this->console = $console;
		$this->renderer = $renderer;
		$this->dispatcher = $dispatcher;
		$this->fileSystem = $fileSystem;
	}

	public function getName() {
		return "generate";
	}

	public function getDescription() {
		return "Generate static site.";
	}

	public function execute($arguments, $flags) {
		$this->path = $arguments[0];
		$this->init();
		// generate articles
		$this->console->write("Generating pages...");
		foreach ($this->articleIterator() as $article) {
			$relative = strtr($article->getRelativePathname(), '\\', '/');
			$result = $this->dispatcher->dispatch($this->path . '/articles/' . $relative);
			if (!array_key_exists('url', $result)) {
				throw new \Exception("Could not determine the URL of article \"$relative\".");
			}
			$page = $this->renderer->render($result, $this->config->get('templates.article'));
			echo $page;
		}
		$this->console->writeLine("done.");
	}

	public function getCommandOptions() {
		$path = new \Phi\CommandOption();
		$path->setDescription("Path to the Phi application directory.")
			 ->setDefault('.')->setRequired()
			 ->setValidator(array($this->fileSystem, 'isDirectory'),
			 	            "Invalid Phi application path");
		return array($path);
	}

	private function articleIterator() {
		foreach ($this->dispatcher->getExtensions() as $extension) {
			$this->finder->name('*.'.$extension);
		}
		$this->finder->files()
		      		 ->ignoreVCS(true)
		       		 ->in($this->path . '/articles');
		return $this->finder;
	}

	private function init() {
		$this->console->write("Loading config file...");
		$this->config->setPath($this->path . '/config.yaml');
		$this->console->writeLine("done.");

		// initialize template engine
		$this->console->write("Initializing renderer...");
		$this->renderer->setTemplatePath($this->path . '/templates');
		$this->console->writeLine("done.");

		// copy assets to destination	
		$this->console->write("Copying assets...");
		$this->fileSystem->copyDirectory($this->path . '/assets', $this->path . '/site');
		$this->console->writeLine("done.");

		// initializing renderer context
		$context = array("sitename" => $this->config->get('sitename'));
		$context = array_merge($context, $this->config->get('context'));
		$this->renderer->setContext($context);
	}
}