<?php

	if( $__return !== null ) {
		die(json_encode($__return));
	}

	if( isset($__db_link) ) {
		mysqli_close($__db_link);
	}

?>