<?php
	
	function _db_connect() {

		$config = parse_ini_file('config.ini'); 

		$link = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
		
		if ($link -> connect_error) {
			_die('Cannot connect to database. ' . $link -> connect_error);
		}

		return $link;
	}

	function _die($str) {
		if( DVPT )
			die($str);
		else
			die('The system is overloaded. Please try in a few minutes.');
	}

	function _log($str) {
		if( DVPT )
			echo $str;
	}

	function _prepare($query) {

		global $__db_link;

		if (!($stmt = $__db_link->prepare($query))) {
		    _die("Prepare failed: (" . $__db_link->errno . ") " . $__db_link->error);
		}

		return $stmt;
	}

	function _execute($stmt) {
		if (!$stmt->execute()) {
		    _die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	}

	function _res($stmt) {
		if (!($res = $stmt->get_result())) {
		    _die("Getting result set failed: (" . $stmt->errno . ") " . $stmt->error);
		}
		return $res;
	}

?>