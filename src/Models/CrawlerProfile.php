<?php

namespace Phobrv\BrvCrawler\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class CrawlerProfile.
 *
 * @package namespace App\Entities;
 */
class CrawlerProfile extends Model implements Transformable {
	use TransformableTrait;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $table = 'crawler_profile';

	protected $fillable = ['id', 'url', 'domain', 'type', 'title_tag', 'content_tag', 'thumb_tag', 'meta_title_tag', 'meta_description_tag', 'is_spread', 'max_crawler', 'is_check'];

	public function crawlerData() {
		return $this->hasMany('Phobrv\BrvCrawler\Models\CrawlerData', 'profile_id');
	}

}
