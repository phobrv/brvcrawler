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
use Yajra\Datatables\Datatables;

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
			$data['crawlerData'] = $this->crawlerDataRepository->all();
			// dd($data['crawlerData']);
			return view('phobrv::crawler.hardword')->with('data', $data);

		} catch (Exception $e) {

		}
	}

	public function getData() {
		$data['crawlerData'] = $this->crawlerDataRepository->all();
		return Datatables::of($data['crawlerData'])
			->addColumn('create', function ($post) {
				return date('d/m/Y', strtotime($post->created_at));
			})
			->addColumn('action', function ($post) {
				return view('phobrv::crawler.components.action', ['post' => $post]);
			})->make(true);
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
		return redirect()->route('crawler.crawlHandwork');

	}

	private function crawlPost($url, $profile) {
		if (!$this->crawlService->checkUrlExist($url) && $this->crawlService->URLIsValid($url)) {
			$html = HtmlDomParser::file_get_html($url);
			$out = $this->crawlElementPost($url, $html, $profile);
			$out['profile_id'] = $profile->id;
			$out['url'] = $url;
			$this->crawlerDataRepository->updateOrCreate($out);

			return "Crawler " . $url . "<br>";
		} else {
			return "Not Crawler " . $url . "</br>";
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
			$out['status'] = config('brvcrawler.crawlerStatus.pending');
		} else {
			$out['status'] = config('brvcrawler.crawlerStatus.fail');
		}
		Log::debug("Time: " . date('Y-m-d H:i:s') . " End crawlElementPost ");
		return $out;
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
					$this->addDraftUrl($e->href, $profile);
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
			$this->addDraftUrl($e->href, $profile);
		}
		$endDate = strtotime("now");
		Log::debug("addDraftUrl " . ($endDate - $startTime));

		return $out;
	}
	private function addDraftUrl($url, $profile) {
		if ($this->crawlService->checkExistDomain($url, $profile->domain) && !$this->crawlService->checkUrlExist($url)) {
			if ($this->crawlService->URLIsValid($url)) {
				$this->crawlerDataRepository->create(
					[
						'profile_id' => $profile->id,
						'url' => $url,
						'status' => config('brvcrawler.crawlerStatus.draft'),
					]
				);
			}
		}
	}

}
