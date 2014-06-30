<?php

namespace Phi;

class ErrorHandler {

	public function __construct() {
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
		echo $error.PHP_EOL;
        exit(1);
    }

}