<?php
	
	include_once('api.header.php');

	$stmt = _prepare("SELECT deviceToken FROM installation");
	
	_execute($stmt);
    $res = _res($stmt);
	$stmt -> close();

	while($row = $res->fetch_assoc()) {
		$url = $_c['server_url'] . '/notificationQuizz.php?deviceToken='.$row['deviceToken'];
		print_r('exec '.$url."\r\n");
		$output = file_get_contents($url);
	}

	include_once('api.footer.php');

?>