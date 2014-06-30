<?php

namespace Phi;

interface Parser {
	public function getExtensions();
	public function parse($text);
}