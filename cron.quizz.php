<?php	
	
	// File called by the cron table
	// This file will trigger a call on notificationQuizz 
	// for each user of the application

	include_once('api.header.php');

	$stmt = _prepare("SELECT deviceToken FROM installation");
	
	_execute($stmt);
    $res = _res($stmt);
	$stmt -> close();

	while($row = $res->fetch_assoc()) {
		$url = $_c['server_url'] . '/notificationQuizz.php?deviceToken='.$row['deviceToken'];
		// For simplicity, we use file_get_contents
		// Improvement to the curl library might be possible
		$output = file_get_contents($url);
	}

	include_once('api.footer.php');

?>