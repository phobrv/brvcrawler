<?php

namespace Phobrv\BrvCrawler\Repositories;

use Phobrv\BrvCrawler\Models\CrawlerProfile;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class CrawlerProfileRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class CrawlerProfileRepositoryEloquent extends BaseRepository implements CrawlerProfileRepository {
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model() {
		return CrawlerProfile::class;
	}

	/**
	 * Boot up the repository, pushing criteria
	 */
	public function boot() {
		$this->pushCriteria(app(RequestCriteria::class));
	}

}
