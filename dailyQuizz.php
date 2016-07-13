<?php
	
	include_once('api.header.php');

	$stmt = _prepare("SELECT q.name FROM quizz q WHERE q.id = ?");

	if (!$stmt->bind_param('i', $_GET['quizz'])) {
		_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	_execute($stmt);

	$res = _res($stmt);
	$stmt -> close();

	if ($res->num_rows > 0) {
		$__return = array(
			'name' => $res->fetch_assoc()['name'],
			'questions' => array()
		);
	}

	$stmt = _prepare("SELECT q.id, q.label, q.answer FROM knowledge k 
		LEFT JOIN installation i ON i.id = k.installation
		LEFT JOIN question q ON q.id = k.question
		WHERE i.deviceToken = ?
		AND q.quizz = ?
		AND DATE(k.time) = CURRENT_DATE()
		ORDER BY rand()
		LIMIT ?");

	if (!$stmt->bind_param('sii', $_GET['deviceToken'], $_GET['quizz'], $_GET['questions'])) {
		_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	_execute($stmt);

	$res = _res($stmt);
	$stmt -> close();

	$questions = array();
	$questionsIds = array();
	$answersNotUsed = array();

	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			$questions[] = $row;
			$questionsIds[] = $row['id'];
		}
	}

	$total = count($questions) * 3; // 6 questions = 18 answers
	$queryIds = implode(',', $questionsIds);

	$sql = "SELECT q.answer FROM question q WHERE quizz = ? AND q.id NOT IN ($queryIds) LIMIT $total";
	$stmt = _prepare($sql);

	if (!$stmt->bind_param('i', $_GET['quizz'])) {
		_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}

	_execute($stmt);

	$res = _res($stmt);
	$stmt -> close();

	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			$answersNotUsed[] = $row['answer'];
		}
	}

	foreach ($questions as $question) {

		$a = array_slice($answersNotUsed, 0, 3);
		$a[] = $question['answer'];
		shuffle($a);

		$__return['questions'][] = array(
			'id' => $question['id'],
			'question' => $question['label'],
			'right_answer' => $question['answer'],
			'answers' => $a
		);

		shuffle($answersNotUsed);
	}

	include_once('api.footer.php');

?>