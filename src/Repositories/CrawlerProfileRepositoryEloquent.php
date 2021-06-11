<?php

namespace Phobrv\BrvCrawler\Repositories;

use Phobrv\BrvCrawler\Models\CrawlerProfile;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CrawlerProfileRepositoryEloquent extends BaseRepository implements CrawlerProfileRepository {
	public function model() {
		return CrawlerProfile::class;
	}

	public function boot() {
		$this->pushCriteria(app(RequestCriteria::class));
	}

	public function getProfileArray() {
		$all = $this->all();
		$out[0] = "-";
		foreach ($all as $p) {
			$out[$p->id] = $p->url;
		}
		return $out;
	}

}
