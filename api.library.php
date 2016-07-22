<?php

	function _db_connect() {
		global $_c;

		$link = @mysqli_connect($_c['db_servername'], $_c['db_username'], $_c['db_password'], $_c['db_name']);
		
		if (!$link) {
			$str = DVPT ? 'Cannot connect to database.' : MESSAGE_ERROR_STANDARD;
			_output(array('error' => $str), 500);
		}

		return $link;
	}

	function _die($str) {
		_output(array('error' => DVPT ? $str : MESSAGE_ERROR_STANDARD), 500);
	}

	function _output($a, $code = 200) {
		header_status($code);
		header('Content-Type: application/json');
		die(json_encode($a));
	}

	function _prepare($query) {
		global $__db_link;
		if (!($stmt = @$__db_link->prepare($query))) {
			$str = DVPT ? "Prepare failed: (".$__db_link->errno.") ".$__db_link->error : MESSAGE_ERROR_STANDARD;
			_output(array('error' => $str), 500);
		}
		return $stmt;
	}

	function _execute($stmt) {
		if (!@$stmt->execute()) {
			$str = DVPT ? "Execute failed: (".$stmt->errno.") ".$stmt->error : MESSAGE_ERROR_STANDARD;
			_output(array('error' => $str), 500);
		}
	}

	function _res($stmt) {
		if (!($res = @$stmt->get_result())) {
			$str = DVPT ? "Getting result set failed: (".$stmt->errno.") ".$stmt->error : MESSAGE_ERROR_STANDARD;
			_output(array('error' => $str), 500);
		}
		return $res;
	}

	function _requireFields($method, $fields) {
		foreach($fields as $k) {
			if(($method == 'get' && !isset($_GET[$k])) or ($method == 'post' && !isset($_POST[$k]))) {
				_output(array('error' => 'FIELD "'.$k.'" IS MISSING!'), 400);
			}
		}
	}

	function apns($deviceToken, $payload) {
	    
	    global $_c;

	    $ctx = stream_context_create();
	    stream_context_set_option($ctx, 'ssl', 'local_cert', $_c['apns_certificate']);
	    stream_context_set_option($ctx, 'ssl', 'passphrase', $_c['apns_password']);

	    $fp = stream_socket_client($_c['apns_server'], $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

	    $body = $payload;

	    $payload = json_encode($body);

	    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	    $result = fwrite($fp, $msg, strlen($msg));

	    fclose($fp);
	}

	function header_status($statusCode) {
	    static $status_codes = null;

	    if ($status_codes === null) {
	        $status_codes = array (
	            100 => 'Continue',
	            101 => 'Switching Protocols',
	            102 => 'Processing',
	            200 => 'OK',
	            201 => 'Created',
	            202 => 'Accepted',
	            203 => 'Non-Authoritative Information',
	            204 => 'No Content',
	            205 => 'Reset Content',
	            206 => 'Partial Content',
	            207 => 'Multi-Status',
	            300 => 'Multiple Choices',
	            301 => 'Moved Permanently',
	            302 => 'Found',
	            303 => 'See Other',
	            304 => 'Not Modified',
	            305 => 'Use Proxy',
	            307 => 'Temporary Redirect',
	            400 => 'Bad Request',
	            401 => 'Unauthorized',
	            402 => 'Payment Required',
	            403 => 'Forbidden',
	            404 => 'Not Found',
	            405 => 'Method Not Allowed',
	            406 => 'Not Acceptable',
	            407 => 'Proxy Authentication Required',
	            408 => 'Request Timeout',
	            409 => 'Conflict',
	            410 => 'Gone',
	            411 => 'Length Required',
	            412 => 'Precondition Failed',
	            413 => 'Request Entity Too Large',
	            414 => 'Request-URI Too Long',
	            415 => 'Unsupported Media Type',
	            416 => 'Requested Range Not Satisfiable',
	            417 => 'Expectation Failed',
	            422 => 'Unprocessable Entity',
	            423 => 'Locked',
	            424 => 'Failed Dependency',
	            426 => 'Upgrade Required',
	            500 => 'Internal Server Error',
	            501 => 'Not Implemented',
	            502 => 'Bad Gateway',
	            503 => 'Service Unavailable',
	            504 => 'Gateway Timeout',
	            505 => 'HTTP Version Not Supported',
	            506 => 'Variant Also Negotiates',
	            507 => 'Insufficient Storage',
	            509 => 'Bandwidth Limit Exceeded',
	            510 => 'Not Extended'
	        );
	    }

	    if ($status_codes[$statusCode] !== null) {
	        $status_string = $statusCode . ' ' . $status_codes[$statusCode];
	        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
	    }
	}
?>