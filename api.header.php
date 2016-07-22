<?php
	
	session_start();

	define('DVPT', true);
	define('MESSAGE_ERROR_STANDARD', "The system is overloaded. Please try in a few minutes.");
	
	$_c = parse_ini_file('config.ini'); 

	include_once('api.library.php');

	$__db_link = _db_connect();
	
	$__return = null;

?>