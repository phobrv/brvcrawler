<?php

namespace Phobrv\BrvCrawler\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use KubAT\PhpSimple\HtmlDomParser;
use Log;
use Phobrv\BrvCore\Services\UnitServices;
use Phobrv\BrvCrawler\Repositories\CrawlerDataRepository;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;
use Phobrv\BrvCrawler\Services\CrawlServices;
use Str;

class CrawlerController extends Controller {
	protected $crawlerProfileRepository;
	protected $crawlerDataRepository;
	protected $unitService;
	protected $crawlService;
	protected $crawlDataStatus;
	public function __construct(
		CrawlerProfileRepository $crawlerProfileRepository,
		CrawlerDataRepository $crawlerDataRepository,
		CrawlServices $crawlService,
		UnitServices $unitService
	) {
		$this->crawlerDataRepository = $crawlerDataRepository;
		$this->crawlerProfileRepository = $crawlerProfileRepository;
		$this->unitService = $unitService;
		$this->crawlService = $crawlService;
		$this->crawlDataStatus = config('option.crawler_data_status');
	}

	public function crawlHandwork() {
		try {
			$data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
				[
					['text' => 'Crawler Hardword', 'href' => ''],
				]
			);

			$data['profiles'] = $this->crawlerProfileRepository->all();
			return view('phobrv::crawler.hardword')->with('data', $data);

		} catch (Exception $e) {

		}

	}

	//API
	public function apiCrawlHandwork(Request $request) {
		$data = $request->all();

		$profile = $this->crawlerProfileRepository->find($data['source']);
		$out = [];
		switch ($profile->type) {
		case 'post':
			$post = $this->crawlPost(trim($profile->url), $profile);
			break;

		case 'category':
			if ($profile->is_spread) {

				$multi = $this->crawlMultiPostSpread($profile);

			} else {
				$multi = $this->crawlMultiPost($profile);
			}
			break;
		}
		$out = $this->crawlService->renderTableReportCrawl($profile->id);
		// return $out;
		return redirect()->route('crawler.crawlHandwork');

	}

	private function crawlPost($url, $profile) {
		if (!$this->crawlService->checkUrlExist($url) && $this->crawlService->URLIsValid($url)) {

			$html = HtmlDomParser::file_get_html($url);

			$out = $this->crawlElementPost($url, $html, $profile);
			dd($out);
			$this->crawlerDataRepository->updateOrCreate($out, [$out['url']]);

			return "Crawler " . $url . "<br>";
		} else {
			return "Not Crawler " . $url . "</br>";
		}
	}

	private function crawlMultiPost($profile) {
		if (!$profile->domain) {
			return;
		}
		$html = HtmlDomParser::file_get_html($profile->url);
		$out = "";
		foreach ($html->find('a') as $e) {
			if ($this->crawlService->checkExistDomain($e->href, $domain)) {
				$out .= $this->crawlPost(trim($e->href), $profile);
			}
		}
		return $out;

	}

	private function crawlMultiPostSpread($profile) {
		if (!$profile->domain) {
			return;
		}

		$draf = $this->crawlerDataRepository->where('profile_id', $profile->id)->where('status', '-3')->first();

		if ($draf) {
			Log::debug("Time: " . date('Y-m-d H:i:s') . " Start crawlPostSpared ");
			$this->crawlPostSpared($draf, $profile);
		} else {
			$checkExistDataOfProfile = $this->crawlerDataRepository->where('profile_id', $profile->id)->count();
			if ($checkExistDataOfProfile == 0) {
				Log::debug("Time: " . date('Y-m-d H:i:s') . " The first ");
				$html = HtmlDomParser::file_get_html($profile->url);
				$out = "";
				foreach ($html->find('a') as $e) {
					$this->addDrafUrl($e->href, $profile);
				}
				return $out;
			} else {
				Log::debug("Time: " . date('Y-m-d H:i:s') . " Crawl end ");
				return "Crawl end";
			}
		}
		$this->crawlMultiPostSpread($profile);
	}

	private function crawlPostSpared($draf, $profile) {

		$html = file_get_html($draf->url);
		$postEle = $this->crawlElementPost($draf->url, $html, $profile);
		$this->crawlerDataRepository->update($postEle, $draf->id);
		$out = "";
		$startTime = strtotime("now");
		foreach ($html->find('a') as $e) {
			$this->addDrafUrl($e->href, $profile);
		}
		$endDate = strtotime("now");
		Log::debug("addDrafUrl " . ($endDate - $startTime));

		return $out;
	}

	private function addDrafUrl($url, $profile) {

		if ($this->crawlService->checkExistDomain($url, $profile->domain) && !$this->crawlService->checkUrlExist($url)) {
			if ($this->crawlService->URLIsValid($url)) {
				$this->crawlerDataRepository->create(
					[
						'profile_id' => $profile->id,
						'url' => $url,
						'status' => '-3',
					]
				);
			}

		}
	}

	private function crawlElementPost($url, $html, $profile) {
		Log::debug("Time: " . date('Y-m-d H:i:s') . " Start crawlElementPost ");
		$out = [];
		$arrayTag = ['title_tag', 'content_tag', 'thumb_tag', 'meta_title_tag', 'meta_description_tag'];
		foreach ($arrayTag as $value) {
			if (isset($profile->$value)) {
				$key = preg_replace("/(_tag)/i", '', $value);
				$out[$key] = $this->crawlService->findByTag($html, $profile->$value);
			}
		}
		if (isset($out['title']) && isset($out['content'])) {
			$out['slug'] = Str::slug($out['title']);
			$out['status'] = '-2';

		} else {
			$out['status'] = '-1';
		}
		Log::debug("Time: " . date('Y-m-d H:i:s') . " End crawlElementPost ");
		return $out;
	}

}
