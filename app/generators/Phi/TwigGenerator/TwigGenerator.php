<?php

namespace Phi\TwigGenerator;

class TwigGenerator implements \Phi\Generator {

	private $fileSystem;
	private $console;
	private $twig;
	private $templatePath;

	public function __construct(\Phi\FileSystem $fileSystem, \Phi\Console $console) {
		$this->fileSystem = $fileSystem;
		$this->console = $console;
	}

	public function getName() {
		return "twig";
	}

	public function generate(\Phi\Context $context) {
		$context = $context->toArray();
		$this->templatePath = $context['site']['project'].'/'.$context['config']['templates'];
		$this->initializeTwig($context);
		$context = $this->renderAssets($context);
		$this->render($context);
	}

	private function render($context) {
		// render pages
		$articles = &$context['site']['articles'];
		$this->addLinks($articles);
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
				foreach ($paginator->getPages() as $p) {
					$context['page']['paginator'] = $p;
					$page = $this->renderPage($context);
					$this->fileSystem->writeRecursively($context['site']['project'].'/'.
						$context['config']['destination'].'/'.$p['relative_url'], $page); 
				}
			} else {
				$page = $this->renderPage($context);
				$this->fileSystem->writeRecursively($context['site']['project'].'/'.
					$context['config']['destination'].'/'.$current['relative_url'], $page); 
			}
		}	
	}

	private function addLinks(&$articles) {
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
	}

	private function renderPage($context) {
		// render page content
		$this->twig->setLoader(new \Twig_Loader_String());
		$context['page']['content'] =
			$this->twig->render($context['page']['content'], $context);
		$this->twig->setLoader(new \Twig_Loader_Filesystem($this->templatePath));
		return $this->twig->render($context['page']['template'], $context);
	}

	private function renderAssets($oldContext) {
		// Preprocessings : render assets, resolve nested template
		foreach ($oldContext['site']['articles'] as $idx => &$raw) {
			if ($raw['template'] == '*asset*') {
				// render asset template (coffeescript, less, etc...)
				$oldContext['page'] = $raw;
				$page = $this->twig->render("{{ page.content }}", $oldContext);
				$this->fileSystem->writeRecursively($projectPath.'/'.
				$oldContext['config']['destination'].'/'.$raw['relative_url'], $page); 
				unset($oldContext['site']['articles'][$idx]);
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