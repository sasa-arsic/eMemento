<?php
	
	// File called by the cron table
	// This file will trigger a call on notificationQuizz 
	// for each user of the application
	
	include_once('api.header.php');

	// For this cron, we will filter the users based on the interval 
	// of notification they choose to have.
	// The possibilities are each  1, 2, 3 or 4 hours
	// In order to make it simplier and smoother, we do it dynamically SQL side
	// The notification start at 9 AM, this is why we substract
	// 9 hours in the forumla below
	$stmt = _prepare("SELECT deviceToken FROM installation WHERE refreshInterval != 0 && (HOUR(NOW())-9) % refreshInterval = 0");
	
	_execute($stmt);
    $res = _res($stmt);
	$stmt -> close();

	while($row = $res->fetch_assoc()) {
		$url = $_c['server_url'] . '/notificationTraining.php?deviceToken='.$row['deviceToken'];
		// For simplicity, we use file_get_contents
		// Improvement to the curl library might be possible
		$output = file_get_contents($url);
	}

	include_once('api.footer.php');

?>