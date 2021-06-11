<?php

namespace Phobrv\BrvCrawler\Services;
use KubAT\PhpSimple\HtmlDomParser;
use Log;
use Phobrv\BrvCrawler\Repositories\CrawlerDataRepository;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;
use Phobrv\BrvCrawler\Services\CommonServices;
use Str;

class CrawlServices {
	protected $crawlerProfileRepository;
	protected $crawlerDataRepository;
	protected $crawlDataStatus;
	protected $commonServices;
	public function __construct(
		CommonServices $commonServices,
		CrawlerProfileRepository $crawlerProfileRepository,
		CrawlerDataRepository $crawlerDataRepository) {
		$this->commonServices = $commonServices;
		$this->crawlerDataRepository = $crawlerDataRepository;
		$this->crawlerProfileRepository = $crawlerProfileRepository;
		$this->crawlDataStatus = config('brvcrawler.crawlerStatusLabel');
	}

	public function crawlPost($url, $profile) {
		if (!$this->checkUrlExist($url) && $this->commonServices->URLIsValid($url)) {
			$html = HtmlDomParser::file_get_html($url);
			$out = $this->crawlElementPost($url, $html, $profile);
			if (!empty($out['content']) && !empty($out['title'])) {
				$out['content'] = $this->handleImageInContent($out['content']);
				$out['profile_id'] = $profile->id;
				$out['url'] = $url;
				$this->crawlerDataRepository->create($out);
				return ['code' => '0', 'msg' => 'Crawl success'];
			}
		} else {
			return ['code' => '1', 'msg' => 'Invalid request'];
		}
	}
	public function crawlElementPost($url, $html, $profile) {
		Log::debug("Time: " . date('Y-m-d H:i:s') . " Start crawlElementPost ");
		$out = [];
		$arrayTag = ['title_tag', 'content_tag', 'thumb_tag', 'meta_title_tag', 'meta_description_tag'];
		foreach ($arrayTag as $value) {
			if (isset($profile->$value)) {
				$key = preg_replace("/(_tag)/i", '', $value);
				$out[$key] = $this->commonServices->findByTag($html, $profile->$value);
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

	public function crawlMultiPost($url, $profile, $level = 0) {
		Log::debug("Time: " . date('Y-m-d H:i:s') . " StartCrawl " . $url);
		$html = HtmlDomParser::file_get_html($url);
		$level = 0;
		foreach ($html->find('a') as $e) {
			$level++;
			$_url = $this->commonServices->handleUrl($e->href, $profile->domain);
			if ($_url && !$this->checkUrlExist($_url)) {
				$this->addDraftUrl($_url, $profile);
				if ($profile->is_spread && $level < 3) {
					$this->crawlMultiPost($_url, $profile, $level);
				}
			}

			// $this->crawlPost(trim($e->href), $profile);
		}
		Log::debug("Time: " . date('Y-m-d H:i:s') . " EndCrawl " . $url);
		return ['code' => '0', 'msg' => 'Crawl success'];
	}

	public function addDraftUrl($url, $profile) {
		if ($this->commonServices->URLIsValid($url) && !$this->checkUrlExist($url)) {
			$this->crawlerDataRepository->create(
				[
					'profile_id' => $profile->id,
					'url' => $url,
					'status' => config('brvcrawler.crawlerStatus.draft'),
				]
			);
		}
	}
	public function checkUrlExist($url) {
		$check = $this->crawlerDataRepository->where('url', $url)->first();
		return ($check) ? true : false;
	}
	public function handleImageInContent($content) {
		$html = HtmlDomParser::str_get_html($content);
		$folder_download = storage_path('app/public/photos/shares/download/');
		foreach ($html->find('img') as $element) {
			$imgSrc = $element->src;
			$imgName = $this->commonServices->filename_from_uri($imgSrc);
			$newImgSrc = env('APP_URL') . '/storage/photos/shares/download/' . $imgName;
			$path = $folder_download . $imgName;
			file_put_contents($path, file_get_contents($imgSrc));
			$content = str_replace($imgSrc, $newImgSrc, $content);
		}
		return $content;
	}

	//////

	public function crawlPostSpared($draf, $profile) {

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

}