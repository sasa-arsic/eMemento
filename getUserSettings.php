<?php

$config = parse_ini_file('././config.ini'); 

//$deviceToken = $_REQUEST['DeviceToken'];
$deviceToken = "8bd8d38e71f87add4fca3e8f7d5ccb957b3967bf90427ab98b9671c0ac95c67d";


// Create connection
$conn = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
// Check connection
if($conn->connect_error) {
  $this->last_error = 'Cannot connect to database. ' . $conn->connect_error;
}


$sql = "SELECT NotificationsState, IntervalHours FROM User WHERE DeviceToken = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $deviceToken);
$stmt->execute();

$result = $stmt->get_result();


if ($result > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
    	$userNoficationState = $row["NotificationsState"];
    	$userIntervalHours = $row["IntervalHours"];
    
		$arr = array('NotificationsState' => $_POST['NotificationsState'] = $userNoficationState, 'IntervalHours' => $_POST['IntervalHours'] = $userIntervalHours);
		echo json_encode($arr);
    }
} else {
    echo "0 results";
}



mysqli_close($conn);

?>


