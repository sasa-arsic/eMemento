<?php
	
	include_once('api.header.php');

	$sql = "SELECT q.label, q.answer FROM knowledge k 
			LEFT JOIN installation i ON i.id = k.installation
			LEFT JOIN question q ON q.id = k.question
			WHERE i.deviceToken = ?
			AND DATE(k.time) = CURRENT_DATE()";

	$stmt = _prepare($sql);

	if (!$stmt->bind_param('s', $deviceToken)) {
		_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}

	_execute($stmt);

	$res = _res($stmt);
	
	$__return[] = $res->fetch_assoc();

	include_once('api.footer.php');

?>


