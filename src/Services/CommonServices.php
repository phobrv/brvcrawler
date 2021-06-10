<?php

namespace Phobrv\BrvCrawler\Services;

class CommonServices {
	public function filename_from_uri($uri) {
		$parts = explode('/', $uri);
		return array_pop($parts);
	}
	public function URLIsValid($URL) {
		$exists = true;
		$file_headers = @get_headers($URL);
		$InvalidHeaders = array('404', '403', '500');
		foreach ($InvalidHeaders as $HeaderVal) {
			if (strstr($file_headers[0], $HeaderVal)) {
				$exists = false;
				break;
			}
		}
		return $exists;
	}
	public function getDomainFromUrl($url) {
		$urlEle = parse_url($url);
		return isset($urlEle['host']) ? $urlEle['host'] : "";
	}
	public function findByTag($html, $tag) {
		foreach ($html->find($tag) as $e) {
			return $e->innertext;
		}
	}
}