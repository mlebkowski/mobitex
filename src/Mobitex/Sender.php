<?php namespace Mobitex;

class Sender {

	const ENDPOINT = 'https://api.mobitex.pl/';
	
	const RESPONSE_FORMAT = '/^Status: (?<status>\d{3}), Id: (?<id>[a-f0-9]{32}), Number: (?<number>\d+)$/';
	
	const URL_SEND = 'sms.php';
	const URL_BALANCE = 'balance.php';
	
	const TYPE_FLASH = 'sms_flash';
	const TYPE_SMS = 'sms';
	const TYPE_CONCAT = 'concat';
	const TYPE_WAP_PUSH = 'wap_push';
	const TYPE_BINARY = 'binary';
	const TYPE_UNICODE = 'unicode';
	const TYPE_UNICODE_CONCAT = 'unicode_concat';
	
	const STATUS_OK = '002';
	const STATUS_UNAUTHORIZED = '001';
	const STATUS_REQUEST_ENTITY_TOO_LONG = '113';
	const STATUS_FORBIDDEN = '114';
	const STATUS_PAYMENT_REQUIRED = '202';
	const STATUS_GENERIC_ERROR = '201';
	
	/**
	 * default country code prefix
	 *
	 */
	public $defaultPrefix = '48';
	
	
	protected $pass, $user, $from;
	protected $defaultType = self::TYPE_SMS;
	protected $httpClient;
	
	public function __construct($user, $md5Pass, $from) {
		return $this->setPass($md5Pass)->setUser($user)->setFrom($from);
		
	}
	public static function create($user, $md5Pass, $from) {
		return new static($user, $md5Pass, $from);
	}
	
	
	/** 
	 * throws Exception
	 **/
	public function checkBallance() {
		$data = $this->get(self::URL_BALANCE);
		list ($status, $value) = array_pad(explode(' ', $data), 2, null);
		
		if ('OK' !== $status) {
			throw new Exception\General($data);
		}
		return floatval($value);
	}
	
	public function sendMessage($phone, $text, $type = null) {
		if (null === $type) {
			$type = $this->defaultType;
		}
	
		// TODO: sprawdzać długość tekstu?
		$phone = $this->formatPhone($phone);
		$result = $this->get(self::URL_SEND, array (
			'number' => $phone,
			'text' => $text,
			'type' => $type,
		));
		
		$result = $this->parseResult($result);
		
		$status = $result['status'];
		$message = $this->getErrorMessage($status);
		
		
		if (self::STATUS_OK === $status) {
			return true;
		}
		
		switch ($result['status']):
		case self::STATUS_PAYMENT_REQUIRED:
			throw new Exception\PaymentRequired($message, $status);
		case self::STATUS_FORBIDDEN:
			throw new Exception\Forbidden($message, $status);
		case self::STATUS_UNAUTHORIZED:
			throw new Exception\Unauthorized($message, $status);
		case self::STATUS_REQUEST_ENTITY_TOO_LONG:
			throw new Exception\RequestEntityTooLong($message, $status);
		default:
			throw new Exception\General($message, $status);
		endswitch;
	}
	
	protected function get($uri, $params = array()) {
		$params = array_merge($params, array (
			'from' => $this->getFrom(),
			'user' => $this->getUser(),
			'pass' => $this->getPass(),
		));

		$url = self::ENDPOINT . $uri . '?' . http_build_query($params);
		
		$httpClient = $this->getHttpClient();
		if (null === $httpClient) {
			$httpClient = new HttpClient();
		}
		
		return $httpClient->request($url);
	} 
	
	public function parseResult($str) {
		$result = array (
			'status' => self::STATUS_GENERIC_ERROR,
			'number' => null,
			'id' => null,
		);
		
		if (preg_match(self::RESPONSE_FORMAT, chop($str), $m)) {
			$result = array_merge($result, $m);
		}
		
		$result['message'] = $this->getErrorMessage($result['status']);

		return $result;
	}
	
	public function formatPhone($phone) {
		$phone = preg_replace('/[^\d]/', '', $phone);
		if (strlen($phone) <= 9) {
			$phone = $this->defaultPrefix . $phone;
		}
		return $phone;
	}
	
	public function setUser($user) {
		$this->user = $user;
		return $this;
	}
	public function getUser() {
		return $this->user;
	}
	
	public function setPass($pass) {
		$this->pass = $pass;
		return $this;
	}
	public function getPass() {
		return $this->pass;
	}
	
	public function setFrom($from) {
		$this->from = $from;
		return $this;
	}
	public function getFrom() {
		return $this->from;
	}
	
	public function setDefaultPrefix($prefix) {
		$prefix = trim(trim($prefix), '+');
		$this->defaultPrefix = $prefix;
	}
	
	public function setHttpClient(HttpClientInterface $httpClient) {
		$this->httpClient = $httpClient;
		return $this;
	}
	public function getHttpClient() {
		return $this->httpClient;
	}
	
	public function setDefaultType($type) {
		$this->defaultType = $type;
		return $this;
	}
	
	public function getErrorMessage($errNo) {
		static $codes = array (
self::STATUS_UNAUTHORIZED => 'Brak autoryzacji, błędny login lub hasło',
self::STATUS_OK => 'Wiadomość SMS została prawidłowo odebrana przez Serwis',
'003' => 'Wiadomość została wysłana do smsc',
'004' => 'Wiadomość odebrana przez odbiorcę (potwierdzenie odbioru)',
'005' => 'Status pośredni, wyjaśnienie w parametrze „err” Tabela 3',
'007' => 'Błąd doręczenia, wyjaśnienie w parametrze „err” Tabela 3',
'010' => 'Wiadomość wygasła z powodu niemożliwości jej dostarczenia do odbiorcy',
'103' => 'Brak pola text w wiadomości lub pole text niepełne (wap push)',
'104' => 'Błędnie wypełnione lub brak pola nadawcy',
self::STATUS_REQUEST_ENTITY_TOO_LONG => 'Pole text jest za długie',
'106' => 'Błędny lub brak pola numer',
'107' => 'Błędny parametr type',
'110' => 'Typ wiadomości nie obsługiwany',
self::STATUS_FORBIDDEN => 'Nieautoryzowany nadawca',
'201' => 'Błąd systemu, natychmiastowy kontakt z administratorem systemu',
self::STATUS_PAYMENT_REQUIRED => 'Brak środków na koncie',
'204' => 'Konto nieaktywne',
'205' => 'Sieć docelowa zablokowana',
'206' => 'Brak autoryzacji dla użytego adresu IP',
'301' => 'Brak lub błędny identyfikator wiadomości',
		);
		
		$code = str_pad($errNo, 3, '0', STR_PAD_LEFT);
		return isset($codes[$code]) ? $codes[$code] : null;
	}
}
