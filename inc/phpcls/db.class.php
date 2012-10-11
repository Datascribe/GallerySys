<?php
/**
 * Class for handling MySQL Queries and Results
 * @author Adam Prescott <adam.prescott@datascribe.co.uk>
 */
class DB {
	var $DBLinkID;
	
	function DB() {
		$connect = @mysql_connect(SQLHost, SQLUsername, SQLPassword);
		if(!$connect) {
			throw new Exception('DB Class - DB: Could not connect to MySQL Host with error: '.mysql_error());
		} else {
			$dbc = @mysql_select_db(SQLDatabase, $connect);
			if(!$dbc) {
				throw new Exception('DB Class - DB: Could not access MySQL Database with error: '.mysql_error());
			} else {
				$this->DBLinkID = $connect;
			}
		}
	}
	
        /**
         * This sanitises and runs a MySQL Query but only if used correctly.
         * 
         * Use <b>%s</b> where you would like the data to be validated and sanitised in the query.
         * The query <b>MUST</b> be in Double Quotes for the <b>%s</b> to be picked up.
         * 
         * <code>
         * $DB->doQuery("SELECT * FROM `users` WHERE `email` = '%s' AND `password` = '%s'", $email, $password);
         * </code>
         * @param string $query,... Additional <b>Parameters</b> are processed in order of <b>%s</b>'s within the query.
         * @return resource A MySQL Query Resource
         * @throws Exception on MySQL Query Error
         */
	function doQuery($query) {
		$numParams = func_num_args();
		$params = func_get_args();
		
		if($numParams > 1) {
			for($i=1;$i<$numParams;$i++) {
				$params[$i] = mysql_real_escape_string($params[$i],$this->DBLinkID);
			}
			$query = call_user_func_array('sprintf', $params);
		}
		$result = @mysql_query($query, $this->DBLinkID);
		if(!$result) {
			throw new Exception('DB Class - doQuery: MySQL Error - '.mysql_error());
		} else {
			return $result;
		}
	}
	
        /**
         * Gets the number of rows from a MySQL Query Resource. Same thing as mysql_num_rows()
         * 
         * @param resource $resource must be the result from doQuery or mysql_query()
         * @return int Number of Rows from a Select Query
         */
	function numRows($resource) {
		$result = mysql_num_rows($resource);
			return $result;
	}
	
        /**
         * Same thing as mysql_fetch_assoc()
         * 
         * @param resource $q must be the result from doQuery or mysql_query()
         * @return array An associative Array
         */
	function getColumns($q) {
		return mysql_fetch_array($q, MYSQL_ASSOC);
	}
}
?>