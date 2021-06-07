<?php
namespace Phobrv\BrvCrawler\ViewComposers;

use Illuminate\View\View;

class CrawComposer {
	public $arrayCrawlerType = [];

	public function __construct() {
		$this->arrayCrawlerType = config('brvcrawler.arrayCrawlerType');
	}

	public function compose(View $view) {
		$view->with('arrayCrawlerType', $this->arrayCrawlerType);
	}
}