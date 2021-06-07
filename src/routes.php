<?php

Route::middleware(['web', 'auth', 'auth:sanctum', 'lang', 'verified'])->namespace('Phobrv\BrvCrawler\Controllers')->group(function () {
	Route::middleware(['can:supperAdmin'])->prefix('admin')->group(function () {
		Route::get('/crawler', 'CrawlerController@test')->name('crawler.test');
		Route::get('/crawl-handwork', 'CrawlerController@crawlHandwork')->name('crawler.crawlHandwork');
		Route::post('/apiCrawlHandwork', 'CrawlerController@apiCrawlHandwork')->name('crawler.apiCrawlHandwork');
		Route::resource('/crawlerProfile', 'CrawlerProfileController')->except(['create', 'show']);
	});
});