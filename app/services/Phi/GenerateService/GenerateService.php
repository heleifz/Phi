<?php

namespace Phi\GenerateService;

use Symfony\Component\Finder\Finder;

class GenerateService implements \Phi\Service {

	private $finder;
	private $dispatcher;
	private $fileSystem;

	public function __construct(Finder $finder, \Phi\ParserDispatcher $dispatcher, \Phi\FileSystem $fileSystem) {
		$this->finder = $finder;
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
		$path = $arguments[0];
		foreach ($this->dispatcher->getExtensions() as $extension) {
			$this->finder->name('*.'.$extension);
		}
		$this->finder->files()
		      		 ->ignoreVCS(true)
		       		 ->in($path . '/articles');
		foreach ($this->finder as $file) {
			$relative = strtr($file->getRelativePathname(), '\\', '/');
			var_dump($this->dispatcher->dispatch($path . '/articles/' . $relative));
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
}