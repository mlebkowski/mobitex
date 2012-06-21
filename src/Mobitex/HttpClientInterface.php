<?php namespace Mobitex;

interface HttpClientInterface {
	public function request($url, $method = 'GET');
}
