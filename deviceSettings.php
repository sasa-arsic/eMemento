<?php
	
	include_once('api.header.php');

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		
		$stmt = _prepare("INSERT INTO installation (deviceToken, refreshInterval) VALUES (?, ?) ON DUPLICATE KEY UPDATE refreshInterval = VALUES(refreshInterval)");
		
		if (!$stmt->bind_param('si', $_POST['deviceToken'], $_POST['refreshInterval'])) {
			_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}
		
		_execute($stmt);
		
		$stmt -> close();

	} else {

		$stmt = _prepare("SELECT i.refreshInterval FROM installation i WHERE i.deviceToken = ?");

		if (!$stmt->bind_param('s', $_GET['deviceToken'])) {
			_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}

		_execute($stmt);

		$res = _res($stmt);

		if ($res->num_rows > 0) {
			$__return = $res->fetch_assoc();
		}

		$stmt -> close();
	}

	include_once('api.footer.php');

?>