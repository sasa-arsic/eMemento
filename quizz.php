<?php
	
	include_once('api.header.php');

    _requireFields('get', array('deviceToken'));

	$sql = "SELECT q.name FROM quizz q
			LEFT JOIN installation i ON i.language = q.language
			WHERE i.deviceToken = ?";

	$stmt = _prepare($sql);

	if (!@$stmt->bind_param('s', $_GET['deviceToken'])) {
		_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	_execute($stmt);

	$res = _res($stmt);

	if ($res->num_rows > 0) {
		$__return = array();
		while($row = $res->fetch_assoc()) {
			$__return[] = $row;
		}
	}

	$stmt -> close();

	include_once('api.footer.php');

?>


