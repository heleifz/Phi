<?php

namespace Phi;

/**
 * Error handler, simply print out all error/exception messages.
 */
class ErrorHandler {

	private $console;

	public function __construct(\Phi\Console $console) {
		$this->console = $console;
		set_error_handler(array($this, 'handleError'));	
		set_exception_handler(array($this, 'handleException'));
	}

	public function handleError($level, $message, $file, $line, $context) {
        if (error_reporting() & $level) {
            throw new \Exception($message);
        }
	}

	public function handleException($exception) {
		$error = sprintf('[ERROR] %s ', $exception->getMessage());
		$this->console->writeLine($error);
        exit(1);
    }

}