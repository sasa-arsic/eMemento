<?php
	
	// If check if we declared any return in our script
	if( $__return !== null ) {
		// if we have a return we let the output function handle it
		_output($__return);
	} else {
		// otherwise we send a success with no result status code
		header_status(204);
	}

	// we check if we have any link open to the DB
	// we currently always open a link but this is there
	// for future optimisation of the _db_connect()
	if( isset($__db_link) ) {
		// we free the link
		mysqli_close($__db_link);
	}

?>