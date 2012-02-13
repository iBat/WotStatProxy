<?php

/**
 * This is a MySQL handler class
 * 
 * @version 1.0
 * @author PhpToys
 * @copyright PhpToys 2007
 *
 */
class DBM{
	
	var $connection   = '';
	var $queryCounter = 0;
	var $totalTime    = 0;	
	var $errorCode    = 0;
	var $errorMsg     = '';
	var $resultSet    = '';
	
	/**
	 * Constuctor of the class.
	 * Connects to the server and selects the database.
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 * @return DBManager
	 */
	function DBManager($host, $user, $pass, $db){
		$startTime = $this->getMicroTime();
		
		// Try to make a connection to the server
		if (!$this->connection = @mysql_connect($host,$user,$pass,true)){
			$this->errorCode = mysql_errno();
			$this->errorMsg  = mysql_error();
			return false;
		}
		
		// Now select the database
		if (!@mysql_select_db($db,$this->connection)){
			$this->errorCode = mysql_errno();
			$this->errorMsg  = mysql_error();
			@mysql_close($this->connection);
			return false;
		}

		$this->totalTime += $this->getMicroTime() - $startTime;
			
		return true;
	}
	
	
	/**
	 * Execute the sql statement and returns with the result set.
	 *
	 * @param string $sql
	 * @return unknown
	 */
	function executeQuery($sql){
		$startTime = $this->getMicroTime();

		++$this->queryCounter;
		
		if(!$this->resultSet = @mysql_query($sql,$this->connection)){
			$this->errorCode = mysql_errno();
			$this->errorMsg  = mysql_error();
			$this->totalTime = $this->getMicroTime() - $startTime;
			return false;
		}
		
		$this->totalTime += $this->getMicroTime() - $startTime;

		return $this->resultSet;
	}
	
	
	/**
	 * This function loads the previous query result into an array.
	 *
	 * @return array
	 */
	function loadResult() {
		$array = array();
		while ($row = mysql_fetch_array( $this->resultSet )) {
			$array[] = $row;
		}
		mysql_free_result( $this->resultSet );

		return $array;
	}	
	
	/**
	 * Returns with the number of selected rows in the previous sql statement.
	 *
	 * @return int
	 */
	function getSelectedRows()
	{
		return @mysql_num_rows($this->resultSet);
	}
	
	/**
	 * Returns with the number of the affected rows in the previous sql statement.
	 *
	 * @return int
	 */
	function getAffectedRows()
	{
		return @mysql_affected_rows($this->connection);
	}	
	
	/**
	 * Get the ID generated from the previous INSERT operation
	 *
	 * @return int
	 */
	function getInsertId(){
		return @mysql_insert_id($this->connection);
	}
	
	/**
	 * Return the total time spended in this class
	 *
	 * @return float
	 */
	function getDBTime(){
		return round($this->totalTime,6);
	}
	
	function getSqlCount(){
		return $this->queryCounter;
	}
	
	function getErrrorCode(){
		return $this->errorCode;
	}
	
	function getErrorMessage(){
		return $this->errorMsg;
	}
	
	
  	function getMicroTime() {
    	list($usec, $sec) = explode(" ",microtime());
    	return ((float)$usec + (float)$sec);
    }
}

?>