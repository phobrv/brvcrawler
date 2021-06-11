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

			$out['content'] = $this->handleImageInContent($out['content']);
			$out['profile_id'] = $profile->id;
			$out['url'] = $url;
			$this->crawlerDataRepository->updateOrCreate($out);
			return ['code' => '0', 'msg' => 'Crawl success'];
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

	public function crawlMultiPost($profile) {
		if (!$profile->domain) {
			return;
		}
		$html = HtmlDomParser::file_get_html($profile->url);
		$out = "";
		foreach ($html->find('a') as $e) {
			if ($this->checkExistDomain($e->href, $domain)) {
				$out .= $this->crawlPost(trim($e->href), $profile);
			}
		}
		return $out;
	}
	public function crawlMultiPostSpread($profile) {
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
	public function addDraftUrl($url, $profile) {
		if ($this->checkExistDomain($url, $profile->domain) && !$this->checkUrlExist($url)) {
			if ($this->commonServices->URLIsValid($url)) {
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

	public function checkUrlExist($url) {
		$check = $this->crawlerDataRepository->where('url', $url)->first();
		return ($check) ? true : false;
	}

}