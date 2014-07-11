<?php

namespace Phi\TwigGenerator;

class TwigGenerator implements \Phi\Generator {

	private $fileSystem;
	private $console;
	private $twig;

	public function __construct(\Phi\FileSystem $fileSystem, \Phi\Console $console) {
		$this->fileSystem = $fileSystem;
		$this->console = $console;
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
		$articles = &$context['site']['articles'];
		$total = count($articles);
		for ($i = 0; $i < $total; $i++) {
			if ($i > 0) {
				$articles[$i]['previous_article'] = &$articles[$i - 1];
			} else {
				$articles[$i]['previous_article'] = NULL;
			}
			if ($i < ($total - 1)) {
				$articles[$i]['next_article'] = &$articles[$i + 1];
			} else {
				$articles[$i]['next_article'] = NULL;
			}
		}
		// rendering every page	
		for ($i = 0; $i < $total; $i++) {
			$current = &$articles[$i];
			$this->console->writeLine('Generating '.$current['relative_url']);
			$context['page'] = &$current; 
			if (isset($current['paginator'])) {
				$paginator = new \Phi\Paginator($current, $context);
				$pages = $paginator->getPages();
				foreach ($pages as $p) {
					$context['page']['paginator'] = $p;
					$page = $this->twig->render($current['template'], $context);
					$this->fileSystem->writeRecursively($context['site']['project'].'/'.
						$context['config']['destination'].'/'.$p['relative_url'], $page); 
				}
			} else {
				$page = $this->twig->render($current['template'], $context);
				$this->fileSystem->writeRecursively($context['site']['project'].'/'.
					$context['config']['destination'].'/'.$current['relative_url'], $page); 
			}
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
				$oldContext['config']['destination'].'/'.$raw['relative_url'], $page); 
				unset($oldContext['site']['articles'][$idx]);
			} else {
				// !! prerender the article content
				// (it probably contains Twig structures)
				$oldContext['page'] = $raw;
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
			'cache' => false,
			'charset' => $context['site']['encoding'],
		));
		$twig->addExtension(new PhiExtension($context));
		$twig->addTokenParser(new JsTokenParser($context['site']['root']));
		$twig->addTokenParser(new CssTokenParser($context['site']['root']));
		$twig->addTokenParser(new FaviconTokenParser($context['site']['root']));
		$this->twig = $twig;
	}
}