<?php

namespace Phi\TwigGenerator;

class TwigGenerator implements \Phi\Generator {

	private $fileSystem;

	public function __construct(\Phi\FileSystem $fileSystem) {
		$this->fileSystem = $fileSystem;
	}

	public function getName() {
		return "twig";
	}

	public function generate($context) {
		$articles = $context['site']['articles'];
		$projectPath = $context['site']['project'];
		// initialize Twig
		$templatePath = $projectPath.'/'.$context['site']['config']['templates'];
		$loader = new \Twig_Loader_Filesystem($templatePath);
		$twig = new \Twig_Environment($loader, array('autoescape' => false));
		$twig->addExtension(new PhiExtension($context));
		// rendering every page	
		$total = count($articles);
		for ($i = 0; $i < $total; $i++) {
			$current = $articles[$i];
			$current['previous_article'] = $i > 0 ? $articles[$i - 1] : NULL;
			$current['next_article'] = $i < ($total - 1) ? $articles[$i + 1] : NULL;
			$context['page'] = $current; 
			$page = $twig->render($current['template'], $context);
			$this->fileSystem->writeRecursively($projectPath.'/'.
				$context['site']['config']['destination'].'/'.$current['url'], $page); 
		}
	}
}