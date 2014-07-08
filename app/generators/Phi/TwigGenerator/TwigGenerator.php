<?php

namespace Phi\TwigGenerator;

class TwigGenerator implements \Phi\Generator {

	private $fileSystem;
	private $twig;

	public function __construct(\Phi\FileSystem $fileSystem) {
		$this->fileSystem = $fileSystem;
	}

	public function getName() {
		return "twig";
	}

	public function generate(\Phi\Context $context) {
		$context = $context->toArray();
		$this->initializeTwig($context);
		$context = $this->preRender($context);
		// Set filesystem loader
		$templatePath = $context['site']['project'].'/'.$context['config']['templates'];
		$loader = new \Twig_Loader_Filesystem($templatePath);
		$this->twig->setLoader($loader);
		$this->render($context);
	}

	private function render($context) {
		// render pages
		$articles = $context['site']['articles'];
		$total = count($articles);
		// rendering every page	
		for ($i = 0; $i < $total; $i++) {
			$current = $articles[$i];
			$current['previous_article'] = $i > 0 ? $articles[$i - 1] : NULL;
			$current['next_article'] = $i < ($total - 1) ? $articles[$i + 1] : NULL;
			$context['page'] = $current; 
			$page = $this->twig->render($current['template'], $context);
			$this->fileSystem->writeRecursively($context['site']['project'].'/'.
				$context['config']['destination'].'/'.$current['relativeUrl'], $page); 
		}	
	}

	private function preRender($oldContext) {
		// Preprocessings : render assets, resolve nested template
		foreach ($oldContext['site']['articles'] as $idx => &$raw) {
			if ($raw['template'] == '*asset*') {
				// render asset template (coffeescript, less, etc...)
				$oldContext['page'] = $raw;
				$page = $this->twig->render("{{ page.content }}", $oldContext);
				$this->fileSystem->writeRecursively($projectPath.'/'.
				$oldContext['config']['destination'].'/'.$raw['relativeUrl'], $page); 
				unset($oldContext['site']['articles'][$idx]);
			} else {
				// !! prerender the article content
				// (it probably contains Twig structures)
				$raw['content'] = $this->twig->render($raw['content'], $oldContext);
			}
		}
		// throw away null entries
		$oldContext['site']['articles'] =
			array_values($oldContext['site']['articles']);
		return $oldContext;
	}

	private function initializeTwig($context) {
		$twig = new \Twig_Environment(new \Twig_Loader_String(), array(
			'autoescape' => false,
			'charset' => $context['site']['encoding']
		));
		$twig->addExtension(new PhiExtension($context));
		$twig->addTokenParser(new JsTokenParser($context['site']['root']));
		$twig->addTokenParser(new CssTokenParser($context['site']['root']));
		$twig->addTokenParser(new FaviconTokenParser($context['site']['root']));
		$this->twig = $twig;
	}
}