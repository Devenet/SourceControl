<?php

define('PHPPREFIX','<?php /* ');
define('PHPSUFFIX',' */ ?>');
define('PATH_DB', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'data/db'.DIRECTORY_SEPARATOR);

abstract class DataBase implements Iterator, Countable, ArrayAccess {

	protected $data;
	private $keys;
	private $current;

	protected $database = 'default_db.php';
	protected $logged;

	function __construct($logged = FALSE)
	{
		$this->database = PATH_DB.$this->database;
		$this->logged = $logged;
		$this->check();
		$this->read();
	}

	// Countable interface implementation
	public function count() { return count($this->data); }

	// ArrayAccess interface implementation
	public function offsetSet($offset, $value)
	{
		if (!$this->logged) die('You have no write right.');
		if (empty($value['id'])) die('An entry should always have an id.');
		if (empty($offset)) die('You must specify a key.');
		$this->data[$offset] = $value;
	}
	public function offsetExists($offset) { return array_key_exists($offset, $this->data); }
	public function offsetUnset($offset) { if (!$this->logged) die('You have no delete right.'); unset($this->data[$offset]); }
	public function offsetGet($offset) { return isset($this->data[$offset]) ? $this->data[$offset] : NULL; }

	// Iterator interface implementation
	function rewind() { $this->keys = array_keys($this->data); sort($this->keys); $this->current=0; }
	function key() { return $this->keys[$this->current]; }
	function current() { return $this->data[$this->keys[$this->current]]; }
	function next() { ++$this->current; }
	function valid() { return isset($this->keys[$this->current]); }

	// Check if db directory and file exists
	private function check()
	{
		if (!file_exists($this->database))
		{
			$this->data = $this->populate();
			file_put_contents($this->database, PHPPREFIX.base64_encode(gzdeflate(serialize($this->data))).PHPSUFFIX);
		}
	}
	// Create an array with default data for first time
	protected abstract function populate();

	// Read database from disk to memory
	private function read()
	{
		$this->data = (file_exists($this->database) ? unserialize(gzinflate(base64_decode(substr(file_get_contents($this->database),strlen(PHPPREFIX),-strlen(PHPSUFFIX))))) : array() );
	}
	// Save database from memory to disk
	public function save()
	{
		if (!$this->logged) die('You are not authorized to update the database.');
		ksort($this->data);
		file_put_contents($this->database, PHPPREFIX.base64_encode(gzdeflate(serialize($this->data))).PHPSUFFIX);
	}

	// Return all data
	public function data()
	{
		ksort($this->data);
		return $this->data;
	}

}
