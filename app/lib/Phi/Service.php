<?php

namespace Phi;

/**
 * Command line service
 */

interface Service {
	public function getName();
	public function getDescription();
	public function execute($arguments, $flags);
	public function getCommandOptions();
}