<?php namespace Mobitex;

class FifoHttpClient implements HttpClientInterface {

	protected $queue = array();
	protected $requests = array ();
	protected $lastRequest = null;
	
	public $default = null;
	
	public function __construct($default = null) {
		return $this->setDefault($default);
	}
	
	public static function create($default = null) {
		return new self($default);
	}
	
	public function setDefault($default) {
		$this->default = $default;
		return $this;
	}
	public function setDefaultRecord() {
		$record = func_get_args();
		$record = $this->prepareRecord($record);
		return $this->setDefault($record);
	}
	
	public function push($param) {
		foreach ((array)$param as $_) array_push($this->queue, $_);
	}
	public function pushRecord() {
		$record = func_get_args();
		$this->push($this->prepareRecord($record));
	}
	
	public function prepareRecord($record) {
		return vsprintf('Status: %03d, Id: %032s, Number: %010d', array_pad($record, 3, null));
	}
	
	public function setQueue(array $queue) {
		$this->queue = $queue;
		return $this;
	}
	public function getLastRequest() {
		return $this->lastRequest;
	}
	public function getRequests() {
		return $this->requests;
	}
	
	public function request($url, $method = 'GET') {
		array_push($this->requests, $this->lastRequest = $url);
		return sizeof($this->queue) ? array_shift($this->queue) : $this->default;
	}
}
