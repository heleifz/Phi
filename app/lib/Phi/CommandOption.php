<?php

namespace Phi;

class CommandOption {

	private $name;
	private $default     = NULL;
	private $required    = false;
	private $boolean     = false;
	private $aliases     = array();
	private $description = "";
	private $validator;

	public function __construct($name = NULL) {
		$this->name      = $name;
		$this->validator = array(function ($input) {return true;}, NULL);
	}

	public function getName() {
		return $this->name;
	}

	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setAliases($aliases) {
		if (is_null($this->name)) {
			return $this;
		}
		$this->aliases = array_unique($aliases);
		return $this;
	}

	public function getAliases() {
		return $this->aliases;
	}

	public function setRequired($required = true) {
		$this->required = $required;
		return $this;
	}

	public function setDefault($val) {
		$this->default = $val;
		return $this;
	}

	public function getDefault() {
		return $this->default;
	}

	public function hasDefault() {
		return !is_null($this->default);
	}

	public function isRequired() {
		return $this->required;
	}

	public function setValidator($validator, $message) {
		$this->validator = array($validator, $message);
		return $this;
	}

	public function getValidator() {
		return $this->validator;
	}

	public function isPositional() {
		return is_null($this->name);
	}

	public function setBoolean($boolean = true) {
		$this->boolean = $boolean;
		return $this;
	}

	public function isBoolean() {
		return $this->boolean;
	}

	public function getHelp() {
		$help = '';

		$isNamed = !$this->isPositional();

		if ($isNamed) {
			$help .= PHP_EOL.' '.(mb_strlen($this->name, 'UTF-8') === 1?
				'-':'--').$this->name;
			if (!empty($this->aliases)) {
				foreach ($this->aliases as $alias) {
					$help .= (mb_strlen($alias, 'UTF-8') === 1?
						'/-':'/--').$alias;
				}
			}
			if (!$this->isBoolean()) {
				$help .= ' '.'<argument>';
			}
		} else {
			$help .= " arg {$this->name}";
		}
		if ($this->hasDefault())
		{
			$help .= "(default ".($this->getDefault()).")";
		}
		$help .= PHP_EOL;

		if ($this->isRequired()) {
			$titleLine = ' Required.';
		} else {
			$titleLine = '';
		}

		$descriptionArray = explode(PHP_EOL, $this->description);
		$description = '';
		foreach ($descriptionArray as $d) {
			$description .= ' '.$d.PHP_EOL;	
		}

		$help .= $titleLine.$description;

		return $help;
	}
}