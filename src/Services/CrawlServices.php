<?php

namespace Phobrv\BrvCrawler\Services;
use Phobrv\BrvCrawler\Repositories\CrawlerDataRepository;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;

class CrawlServices {
	protected $crawlerProfileRepository;
	protected $crawlerDataRepository;
	protected $crawlDataStatus;
	public function __construct(
		CrawlerProfileRepository $crawlerProfileRepository,
		CrawlerDataRepository $crawlerDataRepository) {
		$this->crawlerDataRepository = $crawlerDataRepository;
		$this->crawlerProfileRepository = $crawlerProfileRepository;
		$this->crawlDataStatus = config('option.crawler_data_status');
	}
	public function findByTag($html, $tag) {
		foreach ($html->find($tag) as $e) {
			return $e->innertext;
		}
	}
	public function renderTableReportCrawl($profile_id) {
		$dataRender = $this->crawlerDataRepository->orderBy('created_at', 'desc')->where('profile_id', $profile_id)->whereNotIn('status', ['-1', '-3'])->get();
		$out = "";
		if ($dataRender) {
			foreach ($dataRender as $data) {
				$status = $this->rendCrawlDataStatus($data->status);
				$out .=
				"<tr>" .
				"<td>" . date('Y-m-d', strtotime($data->created_at)) . "</td>" .
				"<td style='width:250px'>" . $data->url . "</td>" .
				"<td style='width:300px'>" . $data->title . "</td>" .
					$status .
					"<td></td></tr>";
			}
		}
		return $out;
	}
	public function checkUrlExist($url) {
		$check = $this->crawlerDataRepository->where('url', $url)->first();
		return ($check) ? true : false;
	}
	public function rendCrawlDataStatus($status) {
		switch ($status) {
		case '-2':
			$out = "<td align='center' style='color:orange'>" . $this->crawlDataStatus[$status] . "</td>";
			break;
		case '1':
			$out = "<td align='center' style='color:green'>" . $this->crawlDataStatus[$status] . "</td>";
			break;
		case '-1':
			$out = "<td align='center' style='color:red'>" . $this->crawlDataStatus[$status] . "</td>";
			break;
		default:
			$out = "<td align='center' style='color:orange'> Unknown </td>";
			break;
		}
		return $out;
	}
	public function getDomainFromUrl($url) {
		$urlEle = parse_url($url);
		return isset($urlEle['host']) ? $urlEle['host'] : "";
	}
}