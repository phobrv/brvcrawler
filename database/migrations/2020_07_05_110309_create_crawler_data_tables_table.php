<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateCrawlerDataTablesTable.
 */
class CreateCrawlerDataTablesTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('brv_crawler_data', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('profile_id')->unsigned();
			$table->foreign('profile_id')->references('id')->on('crawler_profile');
			$table->string('url', 225)->unique();
			$table->string('slug', 225)->nullable();
			$table->string('title', 225)->nullable();
			$table->longText('content')->nullable();
			$table->string('thumb', 225)->nullable();
			$table->longText('meta_title')->nullable();
			$table->longText('meta_description')->nullable();
			$table->longText('meta_keywords')->nullable();
			$table->text('excerpt')->nullable();
			$table->string('status', 20)->default('3');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('brv_crawler_data');
	}
}
