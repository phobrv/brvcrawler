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
		$InvalidHeaders = array('404', '403', '500', '301');
		foreach ($InvalidHeaders as $HeaderVal) {
			if (empty($file_headers[0]) || strstr($file_headers[0], $HeaderVal)) {
				$exists = false;
				break;
			}
		}
		return $exists;
	}

	public function urlGetContent($url) {
		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', $url);
		return [
			'code' => $response->getStatusCode(),
			'body' => $response->getBody(),
		];
	}

	public function getDomainFromUrl($url) {
		$urlEle = parse_url($url);
		return isset($urlEle['host']) ? $urlEle['scheme'] . "://" . $urlEle['host'] : "";
	}
	public function findByTag($html, $tag) {
		foreach ($html->find($tag) as $e) {
			return $e->innertext;
		}
	}

	public function handleUrl($url, $domain) {
		$urlEle = parse_url($url);
		$domainEle = parse_url($domain);
		if (empty($urlEle['host'])) {
			return $domain . $url;
		}
		if ($urlEle['host'] == $domainEle['host']) {
			if ($urlEle['scheme'] != $domainEle['scheme']) {
				$url = str_replace($urlEle['scheme'], $domainEle['scheme'], $url);
			}
			return $url;
		} else {
			return null;
		}
	}
}