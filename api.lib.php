<?php
	
	

	function _db_connect() {
		global $_c;

		$link = mysqli_connect($_c['db_servername'], $_c['db_username'], $_c['db_password'], $_c['db_name']);
		
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

	function apns($deviceToken, $payload) {
	    
	    global $_c;
	    
	    $ctx = stream_context_create();
	    stream_context_set_option($ctx, 'ssl', 'local_cert', $_c['apns_certificate']);
	    stream_context_set_option($ctx, 'ssl', 'passphrase', $_c['apns_password']);

	    $fp = stream_socket_client($_c['apns_server'], $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

	    $body = $payload;
    ]

	    echo $payload = json_encode($body);

	    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	    $result = fwrite($fp, $msg, strlen($msg));

	    if (!$result)
	        echo 'Message not delivered' . PHP_EOL;
	    else
	        echo 'Message successfully delivered '.$message. PHP_EOL;

	    fclose($fp);
	}
?>