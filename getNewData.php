<?php

$config = parse_ini_file('././config.ini'); 

$return = [];

// Create connection
$conn = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
// Check connection
if($conn->connect_error) {
  $this->last_error = 'Cannot connect to database. ' . $conn->connect_error;
}




$sql = "SELECT Title, Subtitle FROM Notification n INNER JOIN NotificationLog nl ON n.idNotification = nl.idNotification";
$stmt = $conn->prepare($sql);
$stmt->execute();

$result = $stmt->get_result();

//if we have some rows
if ($result > 0) {
    while($row = $result->fetch_assoc()) {

  $return[] = [ 
        'title'=> $row['Title'],
        'subtitle'=> $row['Subtitle']
    ];    
}

}
else {
 echo "0 results";
}


echo json_encode($return);

mysqli_close($conn);


?>


