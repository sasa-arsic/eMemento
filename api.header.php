<?php
	
	// Start session for if one day we want to work with it
	// Currently not used
	session_start();

	// Constant defining if the environment is development (=DVPT)
	// set to false to when in production
	define('DVPT', true);

	// Standardized message used in all case in production
	// We avoid to show a "real" message to the user for
	// security issue obviously
	define('MESSAGE_ERROR_STANDARD', "The system is overloaded. Please try in a few minutes.");
	
	// Loading our configuration file containing :
	// DB access, URLs, and all infrastricture information
	$_c = parse_ini_file('config.ini'); 

	// This is our library files, more details into it
	include_once('api.library.php');

	// Establishing a DB connection, currently all our services
	// need a DB connection so we let it here for now
	// ToDo: change it to the services itself once we have
	// a service not requiring data
	$__db_link = _db_connect();

	// This variable will contain our output. This variable is a
	// reserved key word, be aware and careful of not using
	// it for another propose. We use it in api.footer.php
	// to dynamically send the headers + content for the call
	$__return = null;

?>