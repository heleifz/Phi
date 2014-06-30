<?php

namespace Phi;

interface Renderer {
	public function setTemplatePath($path);
	public function setContext($context);
	public function render($data, $template);
}