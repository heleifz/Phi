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
		$projectPath = $context['site']['project'];

		// initialize Twig
		$templatePath = $projectPath.'/'.$context['site']['config']['templates'];
		$twig = new \Twig_Environment(new \Twig_Loader_String(), array(
			'autoescape' => false,
			'charset' => $context['site']['encoding']
		));
		$twig->addExtension(new PhiExtension($context));
		$twig->addTokenParser(new JsTokenParser($context['site']['root']));
		$twig->addTokenParser(new CssTokenParser($context['site']['root']));
		$twig->addTokenParser(new FaviconTokenParser($context['site']['root']));

		// !! pre render the article content (it probably contains many Twig structure)
		foreach ($context['site']['articles'] as &$raw) {
			$raw['content'] = $twig->render($raw['content'], $context);
		}
		$loader = new \Twig_Loader_Filesystem($templatePath);
		$twig->setLoader($loader);

		// render pages
		$articles = $context['site']['articles'];
		$total = count($articles);
		// rendering every page	
		for ($i = 0; $i < $total; $i++) {
			$current = $articles[$i];
			$current['previous_article'] = $i > 0 ? $articles[$i - 1] : NULL;
			$current['next_article'] = $i < ($total - 1) ? $articles[$i + 1] : NULL;
			$context['page'] = $current; 
			$page = $twig->render($current['template'], $context);
			$this->fileSystem->writeRecursively($projectPath.'/'.
				$context['site']['config']['destination'].'/'.$current['relativeUrl'], $page); 
		}
	}
}