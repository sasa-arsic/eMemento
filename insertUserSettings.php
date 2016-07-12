<?php


// Load configuration as an array. Use the actual location of your configuration file
$config = parse_ini_file('././config.ini'); 

// Create connection
$conn = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
// Check connection
if($conn->connect_error) {
  $this->last_error = 'Cannot connect to database. ' . $conn->connect_error;
}


// Read request parameters
$active = $_REQUEST['State'];
$interval = $_REQUEST['IntervalHours'];
//$deviceToken = $_REQUEST['DeviceToken'];
$deviceToken = "8bd8d38e71f87add4fca3e8f7d5ccb957b3967bf90427ab98b9671c0ac95c67d";


// Store values in an array
$returnValue = array('notificationsActive'=>$active, 'interval'=>$interval, 'idWatch'=>$deviceToken);


$sql = "INSERT INTO User (NotificationsState, IntervalHours, DeviceToken)
    VALUES (?, ?, ?)  
    ON DUPLICATE KEY UPDATE NotificationsState = VALUES(NotificationsState), IntervalHours = VALUES(IntervalHours)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $active, $interval, $deviceToken );


/* Execute the statement */
$stmt->execute();

/* close statement */
$stmt->close();

// execute query
$result = mysqli_query($conn, $sql);




// Send back request in JSON format
echo json_encode($returnValue); 


// close connection with the database
mysqli_close($conn);

?>

