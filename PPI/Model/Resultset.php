<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
class PPI_Model_Resultset implements Iterator, ArrayAccess, Countable {

	/**
	* The instance of PDOStatement
	*/
	private $_statement;

	/**
	* The default fetch mode
	*/
	private $_fetchMode  = PDO::FETCH_ASSOC;

	/**
	 * The number of rows returned from this query.
	 *
	 * @var integer
	 */
	private $_countResult = null;

	/**
	* List of the acceptable fetch modes
	*/
	private $_fetchModes = array(
		'assoc'   => PDO::FETCH_ASSOC,
		'numeric' => PDO::FETCH_NUM,
		'object'  => PDO::FETCH_OBJ,
		'both'    => PDO::FETCH_BOTH
	);

	private $_dataPointer = 0;
	private $_rows = array();

	function __construct(PDOStatement $statement) {
		// Config override for fetchmode. If it's a valid fetchmode then we override
		$oConfig = PPI_Helper::getConfig();
		if(isset($oConfig->db->fetchmode) && $oConfig->db->fetchmode != '' && array_key_exists($oConfig->db->fetchmode, $this->_fetchModes)) {
			$this->_fetchMode = $oConfig->db->fetchmode;
		}
		$this->_statement = $statement;
	}

	/**
	 * Fetch the next row from the statement class
	 *
	 * @param string $p_sFetchMode
	 * @todo Make this an isset() lookup instead of in_array()
	 * @return array
	 */
	function fetch($p_sFetchMode = null) {
		// If a custom fetchmode was passed and it's a valid fetch mode then we use it otherwise defaulting to  $this->_fetchMode
		$sFetchMode = ($p_sFetchMode !== null && in_array($p_sFetchMode, $this->_fetchModes)) ? $p_sFetchMode : $this->_fetchMode;
		$row = $this->_statement->fetch($sFetchMode);
		$this->_rows[$this->_dataPointer] = $row;
		$this->_dataPointer++;
		return $row;
	}

	/**
	 * Fetch all the records from the statement class
	 *
	 * @param string $p_sFetchMode
	 * @todo Make this an isset() lookup instead of in_array()
	 * @return array
	 */
	function fetchAll($p_sFetchMode = null) {
		// If a custom fetchmode was passed and it's a valid fetch mode then we use it otherwise defaulting to  $this->_fetchMode
		$sFetchMode = ($p_sFetchMode !== null && in_array($p_sFetchMode, $this->_fetchModes)) ? $p_sFetchMode : $this->_fetchMode;
		return $this->_statement->fetchAll($sFetchMode);
	}

	/**
	 * Count the number of rows returned from the query
	 * @return integer
	 */
	function countRows() {
		return $this->_statement->rowCount();
	}

	/**
	 * Check if an offset exists From the SPL Interface: ArrayAccess
	 * @param integer $offset
	 * @return boolean
	 */
	function offsetExists($offset) {
		return isset($this->_rows[(int) $offset]);
	}


	/**
	 * Get a row from the offset
	 * @param integer $offset The Offset
	 * @reurn mixed
	 */
	function offsetGet($offset) {
		$this->_pointer = (int) $offset;
		return $this->current();
	}

	/**
	 * Remove a record by offset
	 * @param integer $offset
	 */
	function offsetUnset($offset) {

	}

	/**
	 * Set a row's data by offset
	 * @param integer $offset The Offset
	 * @param mixed $value The Value
	 */
	function offsetSet($offset, $value) {
		$this->_rows[(int) $offset] = $value;
	}

	/**
	 * Internal count function from the Countable interface.
	 *
	 * @return integer
	 */
	function count() {
		if($this->_countResult === null) {
			$this->_countResult = $this->countRows();
		}
		return $this->_countResult;
	}

	/**
	 * Get the saved statement object
	 * @return object
	 */
	function getStatement() {
		return $this->_statement;
	}

	/**
	 * Get the row from the current pointer offset. If not encountered before, fetches it
	 * @return mixed
	 */
	function current() {
		if(empty($this->_rows[$this->_dataPointer])) {
			$this->_rows[$this->_dataPointer] = $this->_statement->fetch($this->_fetchMode);
		}
		return $this->_rows[$this->_dataPointer];
	}

	/**
	 * Get the current pointer set - From the SPL Interface: Iterator
	 *
	 * @return integer
	 */
	function key() {
		return $this->_dataPointer;
	}

	/**
	 * Increment the pointer - From the SPL Interface: Iterator
	 * @return void
	 */
	function next() {
		++$this->_dataPointer;
	}

	/**
	 * Rewind the pointer - From the SPL Interface: Iterator
	 * @return void
	 */
	function rewind() {
		$this->_dataPointer = 0;
	}

	/**
	 * Verify if there is another pointer next or we are at the end - From the SPL Interface: Iterator
	 * @return boolean
	 */
	function valid() {
		return $this->_dataPointer < $this->count();
	}

	/**
	 * Convert this object to a string, returning the query used to generate this resultset.
	 *
	 * @return string
	 */
	function __toString() {
		return $this->_statement->queryString;
	}

}
