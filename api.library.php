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

	function apns($deviceToken, $payload) {
	    $pathCert = "/home/clients/1bf904378c1d733fa62ee6765697e6b5/web/users/sasa_arsic/web/Watch/Certificates/";
	    $passphrase = 'Welcome2016';

	    $ctx = stream_context_create();
	    stream_context_set_option($ctx, 'ssl', 'local_cert', $pathCert . 'certif.pem');
	    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

	    $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

	    $body['aps'] = $payload;

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