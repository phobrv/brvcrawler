<?php

return [
	'arrayCrawlerType' => [
		'rss' => 'RSS',
		'category' => 'Category',
		'post' => 'Post',
	],
	'crawlerStatus' => [
		'spread' => '4',
		'draft' => '3',
		'pending' => '2',
		'fail' => '0',
		'success' => '1',
	],
	'crawlerStatusLabel' => [
		'4' => 'Spread',
		'3' => 'Draft',
		'2' => 'Pending', //Crawl complete
		'0' => 'Fail', // Crawl fail
		'1' => 'Success', //Imported into posts table
	],
	'crawlerStatusColor' => [
		'4' => 'blue',
		'3' => 'grey',
		'2' => 'orange', //Crawl complete
		'0' => 'red', // Crawl fail
		'1' => 'green', //Imported into posts table
	],
];