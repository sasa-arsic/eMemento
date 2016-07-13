<?php
	
	session_start();

	define('DVPT', true);

	include_once('api.library.php');

	$__db_link = _db_connect();
	
	$__return = null;

?>