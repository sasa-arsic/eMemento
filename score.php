<?php
	
	include_once('api.header.php');

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		
		_requireFields('post', array('score', 'deviceToken'));

    	$quizz = isset($_POST['quizz']) ? intval($_POST['quizz']) : 1;
    	$score = intval($_POST['score']);

		$stmt = _prepare("INSERT INTO score (installation_id, quizz_id, score, time) SELECT i.id, ?, ?, CURRENT_TIMESTAMP FROM installation i where i.deviceToken = ?");
		
		if (!@$stmt->bind_param('iis', $quizz, $score, $_POST['deviceToken'])) {
			_die("Binding parameters failed: (".$stmt->errno.")".$stmt->error);
		}
		
		_execute($stmt);
		
		$stmt -> close();

	} else {

		_requireFields('get', array('deviceToken'));

		$quizz = isset($_GET['quizz']) ? intval($_GET['quizz']) : 1;

		$stmt = _prepare("SELECT s.score, s.time FROM score s LEFT JOIN installation i ON i.id = s.installation_id WHERE i.deviceToken = ? AND s.quizz_id = ? ORDER BY s.time DESC");

		if (!@$stmt->bind_param('si', $_GET['deviceToken'], $quizz)) {
			_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}

		_execute($stmt);

		$res = _res($stmt);

		if ($res->num_rows > 0) {
			while($row = $res->fetch_assoc())
				$__return[] = $row;
		}

		$stmt -> close();
	}

	include_once('api.footer.php');

?>