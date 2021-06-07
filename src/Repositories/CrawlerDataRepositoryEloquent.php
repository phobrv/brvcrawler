<?php

namespace Phobrv\BrvCrawler\Repositories;

use Phobrv\BrvCrawler\Models\CrawlerData;
use Phobrv\BrvCrawler\Repositories\CrawlerDataRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class CrawlerDataRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class CrawlerDataRepositoryEloquent extends BaseRepository implements CrawlerDataRepository {
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model() {
		return CrawlerData::class;
	}

	/**
	 * Boot up the repository, pushing criteria
	 */
	public function boot() {
		$this->pushCriteria(app(RequestCriteria::class));
	}

}
