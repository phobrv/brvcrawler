<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrawlerProfileTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('crawler_profile', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('url', 225)->unique();
			$table->string('domain', 225)->nullable();
			$table->string('type', 225)->nullable();;
			$table->string('title_tag', 225)->nullable();;
			$table->string('content_tag', 225)->nullable();;
			$table->string('thumb_tag', 225)->nullable();;
			$table->string('meta_title_tag', 225)->nullable();;
			$table->string('meta_description_tag', 225)->nullable();;
			$table->integer('is_spread')->default('0');
			$table->integer('max_crawler')->default('0');
			$table->integer('is_check')->default('1');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('crawler_profile');
	}
}
