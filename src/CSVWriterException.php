<?php
namespace App\Lib\CSVWriter;

class CSVWriterException extends \ErrorException {
	// All the possible error codes
	const INVALID_ENCLOSURE_RULE = 1;
	const UNSUPPORTED_ENCODING = 2;
	const ERROR_WHILE_SAVING_TO_FILE = 3;
	
	/**
	 * @var array
	 */
	private $_details;
	
	public function __construct(string $message, int $code = null, array $details = []) {
		parent::__construct($message, $code);
		$this->_details = $details;
	}
	
	/**
	 * Details about the error (if any)
	 * @return array
	 */
	public function getDetails(): array {
		return $this->_details;
	}
}