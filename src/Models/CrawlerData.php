<?php

namespace Phobrv\BrvCrawler\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class CrawlerData.
 *
 * @package namespace App\Entities;
 */
class CrawlerData extends Model implements Transformable {
	use TransformableTrait;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $table = 'crawler_data';

	protected $fillable = ['profile_id', 'url', 'domain', 'slug', 'title', 'content', 'thumb', 'meta_title', 'meta_description', 'meta_keywords', 'excerpt', 'status'];

	public function crawlerProfile() {
		return $this->belongsTo('Phobrv\BrvCrawler\Models\CrawlerProfile');
	}
}
