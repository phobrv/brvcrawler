<?php

Route::middleware(['web', 'auth', 'auth:sanctum', 'lang', 'verified'])->namespace('Phobrv\BrvCrawler\Controllers')->group(function () {
	Route::middleware(['can:supperAdmin'])->prefix('admin')->group(function () {
		Route::get('/getData', 'CrawlerController@getData')->name('crawler.getData');

		Route::post('/crawler/crawl', 'CrawlerController@crawl')->name('crawler.crawl');

		Route::resource('/crawler', 'CrawlerController')->except(['create', 'show']);

		Route::resource('/crawlerProfile', 'CrawlerProfileController')->except(['create', 'show']);
	});
});