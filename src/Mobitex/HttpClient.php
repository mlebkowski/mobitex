<?php namespace Mobitex;

class HttpClient implements HttpClientInterface {
	public function request($url, $method = 'GET') {
		$c = curl_init($url);
		curl_setopt_array($c, array (
			CURLOPT_METHOD = $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false, // :(
		));
		
		$content = curl_exec($c);
		return $content;
	}
}
