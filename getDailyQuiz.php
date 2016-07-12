<?php

$config = parse_ini_file('././config.ini'); 

$return = [];


// Create connection
$conn = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
// Check connection
if($conn->connect_error) {
  $this->last_error = 'Cannot connect to database. ' . $conn->connect_error;
}

$i = 1;

// GET 6 notification title + subtitle sent during the current day 
do {
	$sql = "SELECT Title, Subtitle, Correct FROM 
	(
    (SELECT n.Title, n.Subtitle, 'OK' as Correct 
    	FROM Notification n 
    	INNER JOIN NotificationLog nl ON n.idNotification = nl.idNotification  
    	WHERE nl.idNotificationLog = $i 
    	AND nl.idUser = 1 
		AND DATE(nl.ReceivedDate) = CURRENT_DATE() 
	)

    UNION ALL 

    (SELECT n.Title, n.Subtitle, 'No' as Correct 
    	FROM Notification n INNER JOIN NotificationLog nl ON n.idNotification = nl.idNotification 
    	WHERE nl.idNotificationLog <> $i
    	AND nl.idUser = 1 
		AND DATE(nl.ReceivedDate) = CURRENT_DATE() 
		ORDER BY RAND() LIMIT 3)
	) 
as T 
ORDER BY RAND()";

$stmt = $conn->prepare($sql);
$stmt->execute();

$result = $stmt->get_result();

$i++;

$count = 0;


//if we have some rows
if ($result > 0) {
    while($row = $result->fetch_assoc()) {

  $return[] = [ 
        'title' . $count => $row['Title'],
        'subtitle' . $count => $row['Subtitle'],
        'correct' . $count => $row ['Correct']
		];    
		$count++; 
}

}
else {
 echo "0 results";
}


} while($i <=6);






echo json_encode($return);

mysqli_close($conn);


?>


