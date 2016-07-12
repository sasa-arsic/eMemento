<?php

	echo json_encode($__return);

	if( isset($__mysqli) ) {
		mysqli_close($__mysqli);
	}
	
?>