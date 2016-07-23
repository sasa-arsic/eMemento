<?php
	
	include_once('api.header.php');

	_requireFields('post', array('ids', 'deviceToken'));

	$ids = $_POST['ids'];

	$stmt = _prepare("UPDATE knowledge SET trained = 1 WHERE id IN ($ids) and installation_id = (SELECT id FROM installation WHERE deviceToken = ?)");
	
	if (!@$stmt->bind_param('s', $_POST['deviceToken'])) {
		_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	_execute($stmt);
	
	$stmt -> close();

	include_once('api.footer.php');

?>