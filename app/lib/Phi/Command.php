<?php
/**
 * A revise version of nategood/commando
 */

namespace Phi;

class Command {

	const OPTION_TYPE_ARGUMENT = 1; // e.g. foo
	const OPTION_TYPE_SHORT    = 2; // e.g. -u
	const OPTION_TYPE_VERBOSE  = 4; // e.g. --username

	private

	$name      = null,
	$description = "",
	$options   = array(),
	$arguments = array(),
	$flags     = array(),

	$tokens           = array(),
	$help             = null,
	$parsed           = false,
	$use_default_help = true;

	public function __construct($options, $tokens = array()) {
		$this->setOptions($options);
		$this->setTokens($tokens);
	}

	/**
	 * @param Option|null $option
	 * @param string|int name
	 * @return Option
	 */
	public function setOptions($options) {

		$this->options = array();
		$this->arguments = array();
		$this->flags = array();
		$this->parsed = false;

		$nameless_option_counter = 0;
		foreach ($options as $option) {
			if ($option->isPositional()) {
				$keys = array($nameless_option_counter++);
			} else {
				$keys = array_merge($option->getAliases(), array($option->getName()));
			}
			foreach ($keys as $key) {
				$this->options[$key] = $option;
			}
		}
	}

	public function setDescription($description) {
		$this->description = $description;	
	}

	public function getDescription() {
		return $this->description;
	}

	public function useDefaultHelp($help = true) {
		$this->use_default_help = $help;
	}

	/**
	 * Rare that you would need to use this other than for testing,
	 * allows defining the cli tokens, instead of using $argv
	 * @param array $cli_tokens
	 * @return Command
	 */
	public function setTokens(array $cli_tokens) {
		// todo also slice on "=" or other delimiters
		$this->tokens = $cli_tokens;
		return $this;
	}

	/**
	 * @throws \Exception
	 */
	private function parseIfNotParsed() {
		if ($this->isParsed()) {
			return;
		}
		$this->parse();
	}

	/**
	 * @throws \Exception
	 */
	public function parse() {

		$this->parsed = true;
		$tokens = $this->tokens;

		// throw away the executed filename
		$this->name = array_shift($tokens);

		$keyvals = array();
		$count   = 0;// standalone argument count

		while (!empty($tokens)) {
			$token = array_shift($tokens);

			list($name, $type) = $this->_parseOption($token);

			if ($type === self::OPTION_TYPE_ARGUMENT) {
				if ($this->hasOption($count)) {
					$keyvals[$count] = $name;
					$count++;
				} else {
					throw new \Exception(sprintf('Unexpected argument : %s', $name));
				}
			} else {
				// Short circuit if the help flag was set and we're using default help
				if ($this->use_default_help === true && $name === 'help') {
					$this->printHelp();
					exit;
				}
				$option = $this->getOption($name);
				if ($option->isBoolean()) {
					// inverse of the default, as expected
					$keyvals[$option->getName()] = !$option->getDefault();
				} else {
					// the next token MUST be an "argument" and not another flag/option
					$token            = array_shift($tokens);
					list($val, $type) = $this->_parseOption($token);
					if ($type !== self::OPTION_TYPE_ARGUMENT) {
						throw new \Exception(sprintf('Unable to parse option %s: Expected an argument', $token));
					}
					$keyvals[$option->getName()] = $val;
				}
			}
		}

		foreach ($this->options as $k => $v) {
			if (is_numeric($k)) {
				$idx = $k;
			} else {
				$idx = $v->getName();
			}
			if (empty($keyvals[$idx])) {
				if ($v->hasDefault()) {
					$keyvals[$idx] = $v->getDefault();
				} elseif ($v->isRequired()) {
					throw new \Exception(sprintf('Required %s %s must be specified',
							$v->isPositional()?
							'option':'argument', $idx));
				}
			}	
		}

		foreach ($keyvals as $k => $v) {
			$option    = $this->options[$k];
			$validator = $option->getValidator();
			if (!call_user_func($validator[0], $v)) {
				throw new \Exception(sprintf('Invalid %s : %s',
						$option->isPositional()?'argument':'flag', $validator[1]));
			}
		}

		// fill in the parsing results
		foreach ($keyvals as $k => $v) {
			if (is_numeric($k)) {
				$this->arguments[$k] = $v;
			} else {
				$this->flags[$k] = $v;
			}
		}
	}

	/**
	 * Has this Command instance parsed its arguments?
	 * @return bool
	 */
	public function isParsed() {
		return $this->parsed;
	}

	/**
	 * @param string $token
	 * @return array [option name/value, OPTION_TYPE_*]
	 */
	private function _parseOption($token) {
		$matches = array();

		if (substr($token, 0, 1) === '-' &&
			!preg_match('/(?P<hyphen>\-{1,2})(?P<name>[a-z][a-z0-9_-]*)/i', $token, $matches)) {
			throw new \Exception(sprintf('Unable to parse option %s: Invalid syntax', $token));
		}

		if (!empty($matches['hyphen'])) {
			$type = (strlen($matches['hyphen']) === 1)?
			self::OPTION_TYPE_SHORT:
			self::OPTION_TYPE_VERBOSE;
			return array($matches['name'], $type);
		}

		return array($token, self::OPTION_TYPE_ARGUMENT);
	}

	/**
	 * @param string $option
	 * @return Option
	 * @throws \Exception if $option does not exist
	 */
	public function getOption($option) {
		if (!$this->hasOption($option)) {
			throw new \Exception(sprintf('Unknown option, %s, specified', $option));
		}
		return $this->options[$option];
	}

	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return array of argument `Option` only
	 */
	public function getArguments() {
		$this->parseIfNotParsed();
		return $this->arguments;
	}

	/**
	 * @return array of flag `Option` only
	 */
	public function getFlags() {
		$this->parseIfNotParsed();
		return $this->flags;
	}

	/**
	 * @param string $option name (named option) or index (annonymous option)
	 * @return boolean
	 */
	public function hasOption($option) {
		return !empty($this->options[$option]);
	}

	/**
	 * @param string $help
	 * @return Command
	 */
	public function setHelp($help) {
		$this->help = $help;
		return $this;
	}

	/**
	 * @return string help docs
	 */
	public function getHelp() {
		$this->attachHelp();

		if (empty($this->name) && isset($this->tokens[0])) {
			$this->name = $this->tokens[0];
		}

		$help = PHP_EOL.' '.$this->name;
		if (strlen($this->getDescription()) > 0) {
			$help .= ' : '.$this->getDescription();
		}
		$help .= PHP_EOL.PHP_EOL;

		$seen = array();
		$keys = array_keys($this->options);
		natsort($keys);
		foreach ($keys as $key) {
			$option = $this->getOption($key);
			if (in_array($option, $seen)) {
				continue;
			}
			$help .= $option->getHelp();
			$seen[] = $option;
		}
		return $help;
	}

	public function printHelp() {
		echo $this->getHelp();
	}

	private function attachHelp() {
		$option = new CommandOption('help');
		$option->setDescription('Show the help page for this command.')
		       ->setBoolean();
		$this->options['help'] = $option;
	}

}