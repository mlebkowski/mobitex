<?php namespace Mobitex;

class TestCase extends \PHPUnit_Framework_TestCase {
	protected $client;
	public function createHttpClient() {
		return $this->client = new FifoHttpClient();
	}
	public function createSender() {
		return Sender::create("user", "pass", "from")->setHttpClient($this->createHttpClient());
	}
}
