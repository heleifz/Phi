<?php

namespace Phi;

/**
 * Read metadata AND main body from path
 */
interface Reader {

	public function setPath($path);

	public function getMetadata();

	public function getBody();
}