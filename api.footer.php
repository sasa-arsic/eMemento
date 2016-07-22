<?php

	if( $__return !== null ) {
		_output($__return);
	} else {
		header_status(204);
	}

	if( isset($__db_link) ) {
		mysqli_close($__db_link);
	}

?>