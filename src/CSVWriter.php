<?php
namespace CSVWriter;

class CSVWriter {
	const ENCLOSURE_NONE = 1;
	const ENCLOSURE_ALL = 2;
	
	/**
	 * Buffer of CSV lines
	 * @var string[]
	 */
	private $_lines = [];
	/**
	 * Column delimiter
	 * @var string
	 */
	private $_columnDelimiter = ',';
	/**
	 * New line delimiter
	 * @var string
	 */
	private $_newLineDelimiter = "\n";
	/**
	 * Rule for enclosure
	 * @var int (self::ENCLOSURE_*)
	 */
	private $_enclosureRule = self::ENCLOSURE_ALL;
	/**
	 * Max number of lines the buffer will store before saving to a file
	 * @var int
	 */
	private $_maxLinesBuffer = 0; // 0 => No limit
	/**
	 * Filename to save the CSV
	 * @var string
	 */
	private $_fileName;
	/**
	 * Input encoding, according to mb_list_encodings
	 * Ex: ISO-8859-1, UTF-8
	 * @var string
	 */
	private $_inputEncoding = null;
	/**
	 * Output encoding (to convert to from input encoding), according to mb_list_encodings
	 * Ex: UTF-8, ISO-8859-1
	 * @var string
	 */
	private $_outputEncoding = null;
	
	/**
	 * Class constructor
	 * @param string $fileName File to save the CSV content
	 */
	public function __construct(string $fileName = null) {
		$this->_fileName = $fileName;
	}
	
	/**
	 * Sets the enclosure rule 
	 * @param int $type Passar self::ENCLOSURE_*
	 * @throws CSVWriterException
	 */
	public function setEnclosureRule(int $type): self {
		if (!in_array($type, [self::ENCLOSURE_ALL, self::ENCLOSURE_NONE])) {
			throw new CSVWriterException(
				'Invalid enclosure rule.',
				CSVWriterException::INVALID_ENCLOSURE_RULE
			);
		}
		$this->_enclosureRule = $type;
		return $this;
	}
	
	/**
	 * Returns the configured enclosure rule
	 * @return int
	 */
	public function getEnclosureRule(): int {
		return $this->_enclosureRule;
	}
	
	/**
	 * Sets the column delimiter (generally "," or ";" character)
	 * @param string $delimitador
	 * @return self
	 */
	public function setColumnDelimiter(string $delimiter): self {
		$this->_columnDelimiter = $delimiter;
		return $this;
	}
	
	/**
	 * Returns the configured column delimiter
	 * @return string
	 */
	public function getColumnDelimiter(): string {
		return $this->_columnDelimiter;
	}
	
	/**
	 * Sets the new line delimiter (\n is the default one)
	 * @param string $val
	 */
	public function setNewLineDelimiter(string $val): self {
		$this->_newLineDelimiter = $val;
		return $this;
	}
	
	/**
	 * Returns the configured new line delimiter
	 * @return string
	 */
	public function getNewLineDelimiter(): string {
		return $this->_newLineDelimiter;
	}
	
	/**
	 * Sets the max number of lines the buffer will store before saving to a file
	 * Obs: 0 means no limit
	 * @param int $val
	 */
	public function setMaxLinesBuffer(int $val): self {
		$this->_maxLinesBuffer = intval($val);
		return $this;
	}
	
	/**
	 * Returns the configured maximum number of lines the buffer will store before saving to a file
	 * @return int
	 */
	public function getMaxLinesBuffer(): int {
		return $this->_newLineDelimiter;
	}
	
	/**
	 * Output filename (including path)
	 * @param string $val
	 */
	public function setFileName(string $val = null): self {
		$this->_fileName = $val;
		return $this;
	}
	
	/**
	 * Returns the configured output filename
	 * @param string|null
	 */
	public function getFileName() {
		return $this->_fileName;
	}
	
	
	/**
	 * Sets input and output encoding, according to mb_list_encodings.
	 * Ex: ISO-8859-1, UTF-8
	 * Only set the encondings in case of you want to make the conversion
	 * @param string $input Input encoding, according to mb_list_encodings
	 * @param string $output Output encoding, according to mb_list_encodings
	 * @throws CSVWriterException
	 */
	public function setEncodings(string $input, string $output): self {
		$encodings = mb_list_encodings();
		if (!in_array($input, $encodings, true)) {
			throw new CSVWriterException(
				'Unsupported encoding: '.$input,
				CSVWriterException::UNSUPPORTED_ENCODING
			);
		}
		if (!in_array($output, $encodings, true)) {
			throw new CSVWriterException(
				'Unsupported encoding: '.$output,
				CSVWriterException::UNSUPPORTED_ENCODING
			);
		}
		$this->_inputEncoding = $input;
		$this->_outputEncoding = $output;
		return $this;
	}
	
	/**
	 * Adds a line to CSV
	 * Obs: When the maximum buffer size is reached, it saves to disk
	 * @param array $values Each array element turns into one CSV column
	 * @throws CSVWriterException
	 */
	public function addLine(array $values) {
		$this->_lines[] = $values;
		if ($this->_maxLinesBuffer && $this->_fileName && count($this->_lines) >= $this->_maxLinesBuffer) {
			if (!$this->save()) {
				throw new CSVWriterException(
					'Error during saving to the file to clear the buffer. File: '.$this->_fileName,
					CSVWriterException::ERROR_WHILE_SAVING_TO_FILE
				);
			}
			$this->clearBuffer();
		}
	}
	
	/**
	 * Clears the lines buffer
	 * @return self
	 */
	public function clearBuffer(): self {
		$this->_lines = [];
		return $this;
	}

	/**
	 * Resets the configuration to it's default
	 * @return self
	 */
	public function resetConfig(): self {
		$this->_columnDelimiter = ',';
		$this->_newLineDelimiter = "\n";
		$this->_enclosureRule = self::ENCLOSURE_ALL;
		$this->_fileName = null;
		$this->_inputEncoding = null;
		$this->_outputEncoding = null;
		$this->_maxLinesBuffer = 0;
		return $this;
	}
	
	/**
	 * Resets the configurations and clears the buffer
	 * @return self
	 */
	public function reset(): self {
		return $this->resetConfig()->clearBuffer();
	}
	
	/**
	 * Returns the generated CSV content in the buffer
	 * @return string
	 */
	public function getCSV(): string {
		$lines = [];
		foreach ($this->_lines as $row) {
			if ($this->_enclosureRule == self::ENCLOSURE_ALL) {
				$lines[] = '"'.implode('"'.$this->_columnDelimiter.'"', $row).'"';
			} else { // self::ENCLOSURE_NONE
				$lines[] = implode($this->_columnDelimiter, $row);
			}
		}
		
		$csv = implode($this->_newLineDelimiter, $lines);
		if ($this->_outputEncoding) {
			if (mb_detect_encoding($csv.' ', $this->_outputEncoding, true) === false) {
				$csv = mb_convert_encoding($csv, $this->_outputEncoding, $this->_inputEncoding);
			}
		}
		return $csv;
	}
	
	/**
	 * Salva o CSV gerado em um arquivo
	 * @param string $fileName Filename to save. If null, the previous filename set in the class will be used (via constructor or setFileName method)
	 * @param boolean $append true Dertermine whether to appends the content or not (false will overwrite the file)
	 * @return boolean
	 */
	public function save(string $fileName = null, bool $append = true): bool {
		if (!$fileName) {
			if (!$this->_fileName) {
				return false;
			}
			$fileName = $this->_fileName;
		}
		
		$fp = fopen($fileName, $append ? 'a' : 'w');
		if (!$fp) {
			return false;
		}
		$ok = fwrite($fp, $this->getCSV().$this->_newLineDelimiter) !== false;
		fclose($fp);
		return $ok;
	}
}