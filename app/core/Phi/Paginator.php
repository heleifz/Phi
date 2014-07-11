<?php

namespace Phi;

class Paginator {

	private $article;
	private $context;

	public function __construct($article, $context) {
		$this->validatePaginator($article);
		$this->article = $article;
		$this->context = $context;
	}

	public function getPages() {
		$pages = $this->fillPageUrl($this->article['paginator']);
		if (count($pages) > 0) {
			$pages[0]['page_url'] = $this->article['url'];
			$pages[0]['relative_url'] = $this->article['relative_url'];
		}
		$perPage = $this->article['paginator']['per_page'];
		$total = count($pages);
		for ($i = 0; $i < $total; $i++) {
			$pages[$i]['page_articles'] =
				array_slice($this->context['site']['articles'], $i * $perPage, $perPage);
			$pages[$i]['total'] = $total;
		}
		for ($i = 0; $i < $total; $i++) {
			if ($i > 0) {
				$pages[$i]['previous_page'] = &$pages[$i - 1];
			} else {
				$pages[$i]['previous_page'] = NULL;
			}
			if ($i < $total - 1) {
				$pages[$i]['next_page'] = &$pages[$i + 1];
			} else {
				$pages[$i]['next_page'] = NULL;
			}
		}
		return $pages;
	}

	private function totalPage() {
		return (int)ceil(count($this->context['site']['articles']) /
				    $this->article['paginator']['per_page']);
	}

	private function fillPageUrl($paginator) {
		$totalPage = $this->totalPage();
		$result = array();
		for ($i = 0; $i < $totalPage; $i++) {
			$resolved = \Phi\VariableResolver::resolveArray($paginator,
				array('num' => $i + 1));
			$resolved['relative_url'] = Utils::normalizeUrl($resolved['page_url']);
			$resolved['page_url'] = $this->context['site']['root'] . $resolved['relative_url'];
			$result[] = $resolved;
		}
		return $result;
	}

	private function validatePaginator($article) {
		if (!isset($article['paginator'])) {
			throw new \Exception('Paginator is not configured.');
		}
		if (!isset($article['paginator']['page_url'])) {
			throw new \Exception('Page URL for paginator is not configured.');
		}
		if (!isset($article['paginator']['per_page'])) {
			throw new \Exception('Article per page for paginator is not configured.');
		}
		if (!Utils::isInt($article['paginator']['per_page'])) {
			throw new \Exception('Article per page must be integer number.');
		}
	}

}