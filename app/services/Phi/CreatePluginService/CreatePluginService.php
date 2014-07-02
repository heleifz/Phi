<?php

namespace Phi\CreatePluginService;

class CreatePluginService implements \Phi\Service {

	private $util;
	private $fileSystem;
	private $path;

	private $bolerplates = array(
		'parser' => 'Parser.template',
		'generator' => 'Generator.template'
	);

	private $suffixes = array(
		'parser' => 'Parser',
		'generator' => 'Generator'
	);

	public function __construct(\Phi\Utils $util, \Phi\FileSystem $fileSystem) {
		$this->util = $util;
		$this->fileSystem = $fileSystem;
	}

	public function getName() {
		return "create-plugin";
	}

	public function getDescription() {
		return "Create a Phi plugin bolerplate.";
	}

	public function execute($arguments, $flags) {
		$path = $arguments[0];
		$flags['name'] = ucfirst($flags['name']);
		$className = $flags['name'].$this->suffixes[$flags['type']];
		$destination = $path.'/plugins/'.$className.'.php';
		$templatePath = __DIR__.'/'.$this->bolerplates[$flags['type']];
		$content = $this->fileSystem->read($templatePath);
		$flags['classname'] = $className;
		$flags['name'] = $this->nameTransform($flags['name']);
		$content = $this->util->insertVariables($content, $flags);
		$this->fileSystem->writeRecursively($destination, $content);
	}

	public function getCommandOptions() {
		$path = new \Phi\CommandOption();
		$path->setDescription("Path to the project.")->setDefault('.')->setRequired()
			 ->setValidator(array($this->fileSystem, 'isValidPath'), "Invalid path.");
		$type = new \Phi\CommandOption("type");
		$type->setDescription("Type of plugin (parser/generator).")
			 ->setDefault('parser')->setRequired()->setAliases(array('t'))
			 ->setValidator(function ($t) {
			 	return in_array($t, array('parser', 'generator'));
			 }, "Invalid plugin type.");
		$name = new \Phi\CommandOption("name");
		$name->setDescription("Name of your plugin.(CamelCase)")
			 ->setRequired()->setAliases(array('n'))
			 ->setValidator(function ($n) {
			 	return preg_match('/^[a-zA-Z]*$/', $n);
			 }, 'Invalid plugin name.');
		return array($path, $type, $name);
	}

	private function nameTransform($input) {
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
		$ret = $matches[0];
		foreach ($ret as &$match) {
			$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
		}
		return implode('-', $ret);
	}
}