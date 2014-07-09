<?php

namespace Phi\GenerateService;

class GenerateService implements \Phi\Service {

	private $console;
	private $context;
	private $renderer;
	private $fileSystem;
	private $pluginManager;
	private $articleReader;

	private $path;

	public function __construct(\Phi\PluginManager $pluginManager,
								ArticleReader $articleReader,
								\Phi\GeneratorDispatcher $generator,
								\Phi\FileSystem $fileSystem,
								\Phi\Console $console,
								\Phi\Context $context) {

		$this->pluginManager = $pluginManager;
		$this->context = $context;
		$this->console = $console;
		$this->articleReader = $articleReader;
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
		$sources = $this->getArticlePath($this->path.'/'.$this->context->get('config.source'));
		$articles = array_map(array($this->articleReader, 'read'), $sources);
		$urlMap = $this->generateUrlMap($articles);
		$this->context->merge(new \Phi\Context($articles), 'site.articles');
		$this->context->merge(new \Phi\Context($urlMap), 'site.url_map');
		$generatorName = $this->context->get('config.generator');
		$this->console->write('Generating pages...');
		$this->generator->dispatch($generatorName)->generate($this->context);
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

	private function generateUrlMap($articles) {
		$result = array();
		foreach ($articles as $article) {
			$result[$article['id']] = $article['url'];
		}
		return $result;
	}

	private function getArticlePath($path) {
		return $this->fileSystem->walk($path, true /* ignore VCS */,
			$this->articleReader->getExtensions(),
			$this->context->get('config.exclude_path') ? $this->context->get('config.exclude_path') : array(),
			$this->context->get('config.exclude_name') ? $this->context->get('config.exclude_name') : array());
	}

	private function initialize($path) {
		$this->path = $path;
		// load default configuration add merge it with application's
		$this->context->merge(\Phi\Context::fromYML($path.'/config.yaml'), 'config');
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
		$baseContext = new \Phi\Context(array(
			'site' => array(
				'name' => $this->context->get('config.name'),
				'encoding' => $this->context->get('config.encoding'),
				'root' => $this->context->get('config.root'),
				'project' => $this->path,
				'time' => date("Y-m-d H:i:s"),
			)
		));
		$this->context->merge($baseContext);
	}

}