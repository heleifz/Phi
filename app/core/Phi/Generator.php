<?php

namespace Phi;

interface Generator {
	public function getName();
	public function generate(\Phi\Context $context);
}