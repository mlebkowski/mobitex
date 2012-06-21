<?php namespace Mobitex;

class SenderTest extends TestCase {

	/**
	 * @dataProvider providerValidBalance
	 */
	public function testCheckBalance($value) {
		$sender = $this->createSender();
		$client = $sender->getHttpClient();
		
		$value = (float)$value;
		$client->push(sprintf('OK %f', $value));
		$ballance = $sender->checkBallance();
		$this->assertEquals($value, $ballance);
	}
	public function providerValidBalance() {
		return array (
			'integer' => array(10),
			'float' => array(15.12),
			'float_4_decimal' => array(5.1234),
			'negative' => array (-10),
			'negative_float' => array (-10.2),
			'zero' => array (0),
		);
	}
	
	/**
	 * @expectedException        Mobitex\Exception\General
	 */
	public function testCheckBallanceError() {
		$sender = $this->createSender();
		$client = $sender->getHttpClient();
		
		$client->push('ERROR 001');
		$sender->checkBallance();
	}

	/**
	 * @expectedException        Mobitex\Exception\General
	 */
	public function testCheckBallanceEmptyResponse() {
		$sender = $this->createSender();
		$client = $sender->getHttpClient();
		
		$sender->checkBallance();
	}

	public function testSendMessageCorrect() {
		$sender = $this->createSender();
		$client = $sender->getHttpClient();
		
		$client->pushRecord(Sender::STATUS_OK);
		$result = $sender->sendMessage('501100100', "Hello World!");

		$this->assertTrue($result);
	}
	
	/**
	 * @expectedException		Mobitex\Exception\PaymentRequired
	 **/
	public function testSendMessagePaymentError() {
		$sender = $this->createSender();
		$client = $sender->getHttpClient();
		
		$client->pushRecord(Sender::STATUS_PAYMENT_REQUIRED);
		$sender->sendMessage('501100100', "Hello World!");
	}
	
	/**
	 * @dataProvider providerValidPhoneNumbers
	 **/
	public function testFormatPhone($phone, $expected) {
		$sender = $this->createSender();
		$actual = $sender->formatPhone($phone);
		$this->assertEquals($expected, $actual);
	}
	
	public function providerValidPhoneNumbers() {
		return array (
			'9digit' => array('555111666', '48555111666'),
# 			'10digit' => array('5051112225', '485551112225'),
			'digit_spaces' => array('555 111 666', '48555111666'),
			'9digit_prefix' => array('+48555111666', '48555111666'),
			'9digit_prefix_no_plus' => array('48555111666', '48555111666'),
		);
	}

};
