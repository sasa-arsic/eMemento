<?php

$config = parse_ini_file('././config.ini'); 

$return = [];


// Create connection
$conn = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
// Check connection
if($conn->connect_error) {
  $this->last_error = 'Cannot connect to database. ' . $conn->connect_error;
}


// TROUVER COMMENT INCREMENTER le $i pour avoir d'autre question quand on appelle le script
$i = 1;

// GET 1 question with his correct and wrong answers from one notification sent during the current day 
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


//if we have some rows
if ($result > 0) {
    while($row = $result->fetch_assoc()) {

  $return[] = [ 
        'title' => $row['Title'],
        'subtitle' => $row['Subtitle'],
        'correct' => $row ['Correct']
		];    
}

}
else {
 echo "0 results";
}











echo json_encode($return);

mysqli_close($conn);


?>


