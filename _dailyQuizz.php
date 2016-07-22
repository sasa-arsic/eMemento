<?php
	
	include_once('api.header.php');

    $max = isset($_GET['max']) ? intval($_GET['max']) : 3;
    $quizz = isset($_GET['quizz']) ? intval($_GET['quizz']) : 1;

	$stmt = _prepare("SELECT q.id, q.label, q.answer FROM knowledge k 
		LEFT JOIN installation i ON i.id = k.installation
		LEFT JOIN question q ON q.id = k.question
		WHERE i.deviceToken = ? AND q.quizz = ? AND DATE(k.time) = CURRENT_DATE()
		ORDER BY rand() LIMIT ?");

	if (!$stmt->bind_param('sii', $_GET['deviceToken'], $quizz, $max)) {
		_die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
	}
	
	_execute($stmt);

	$res = _res($stmt);
	$stmt -> close();

	$questionsList = array();
	$questionsIds = array();
	$answersNotUsed = array();

	if ($res->num_rows > 0) {
		while($row = $res->fetch_assoc()) {
			$questionsList[] = $row;
			$questionsIds[] = $row['id'];
		}

		$total = count($questionsList) * 3; // 6 questions = 18 answers
		$queryIds = implode(',', $questionsIds);

		$stmt = _prepare("SELECT q.answer FROM question q WHERE quizz = ? AND q.id NOT IN ($queryIds) LIMIT $total");

		if (!$stmt->bind_param('i', $quizz)) {
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

		foreach ($questionsList as $question) {
			shuffle($answersNotUsed);
			$a = array_slice($answersNotUsed, 0, 3);
			$a[] = $question['answer'];
			shuffle($a);
			$data[] = array(
				'id' => $question['id'],
				'key' => $question['label'],
				'answer' => $question['answer'],
				'values' => $a
			);
		}
	
		$payload = array(
	        'aps' => array(
	            'alert' => array(
	                'body' => 'Test your new knowledge',
	                'title' => 'Daily quizz'
	            ),
	            'category' => 'quizz',
	            'sound' => 'default'
	        ),
	        'data' => $data
	    );
	    
	    print_r($payload);
	    /*
	    apns($_GET['deviceToken'], $payload);
	    */
	}

	include_once('api.footer.php');

?>