<?php

// Load configuration as an array. Use the actual location of your configuration file
$config = parse_ini_file('././config.ini'); 


//$deviceToken = $_REQUEST['DeviceToken'];
$deviceToken = '8bd8d38e71f87add4fca3e8f7d5ccb957b3967bf90427ab98b9671c0ac95c67d';

$return = [];
$i = 0;
$user_notification_state;
$timestamp = date('Y-m-d H:i:s');



// Create connection
$conn = mysqli_connect($config['servername'], $config['username'], $config['password'], $config['dbname']);
// Check connection
if($conn->connect_error) {
  $this->last_error = 'Cannot connect to database. ' . $conn->connect_error;
}



// STEP 0 : get the user active state to see if we send the notification or not
$query0 = "SELECT NotificationsState FROM User WHERE DeviceToken = ?";


$stmt = $conn->prepare($query0);
$stmt->bind_param('s', $deviceToken);
$stmt->execute();

$result = $stmt->get_result();


//if we have a user row
if ($result > 0) {
    // store the notificationstate
    while($row = $result->fetch_assoc()) {

        $user_notification_state = $row["NotificationsState"];
    }

    echo $user_notification_state;
}
else {
 echo "0 results";
}



// ID user for test = 1
$idUser = 1;



if ( $user_notification_state == 1) {

    // STEP 1 :select 3 random notification that were not sent already

        $query1 = "SELECT * FROM Notification WHERE NOT EXISTS 
        (SELECT NotificationLog.idNotification 
            FROM NotificationLog 
            WHERE NotificationLog.idUser = ? 
            AND NotificationLog.idNotification = Notification.idNotification) 
            ORDER by RAND() 
            LIMIT 3";

    // prepare and execute the query
$stmt = $conn->prepare($query1);
$stmt->bind_param('i', $idUser);
$stmt->execute();

$result1 = $stmt->get_result();

        if ($result1 > 0) {
        // output data of each row
            while($row = $result1->fetch_assoc()) {

                $idNotification = $row["idNotification"];
                $title = $row['Title'];
                $subtitle = $row['Subtitle'];

                //STEP 2 :
                // insert in Notification Log
                $query2 = "INSERT INTO NotificationLog (ReceivedDate, idNotification) VALUES (?, ?)";
                $stmt = $conn->prepare($query2);

                // si => s est mis pour la première valeur => timestamp qui est de type String
                // si => i est mis pour la deuxième valeur => idNotification qui est de type nombre entier
                $stmt->bind_param("si", $timestamp, $idNotification);
 

                /* Execute the statement */
                $stmt->execute();

                /* close statement */
                $stmt->close();



                // execute query
                $result2 = mysqli_query($conn, $query2);
            }
        }
        else {
         echo "0 results";
     }



    //-------

      



//STEP 4 :
// send one notification to the user to come to learn new things
//sendNotificationToPhone($deviceToken, $message);


}

// print array
//echo json_encode($return); 





// close connection with the database
mysqli_close($conn);






function sendNotificationToPhone($deviceToken) {



    $pathCert = "/home/clients/1bf904378c1d733fa62ee6765697e6b5/web/users/sasa_arsic/web/Watch/Certificates/";

    $passphrase = 'Welcome2016';

    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', $pathCert . 'certif.pem');
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);


// Open a connection to the APNS server DEVELOPPEMENT
    $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', 
        $err, 
        $errstr, 
        60, 
        STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, 
        $ctx);

//if (!$fp)
//exit("Failed to connect amarnew: $err $errstr" . PHP_EOL);

//echo 'Connected to APNS' . PHP_EOL;

// Create the payload body
    $body['aps'] = array(
        'alert' => 'Daily quiz!',
        'category' => 'QuizInvitation',
        'sound' => 'default'
        );

    echo $payload = json_encode($body);

// Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));

    if (!$result)
        echo 'Message not delivered' . PHP_EOL;
    else
        echo 'Message successfully delivered'.$message. PHP_EOL;

// Close the connection to the server
    fclose($fp);

}





?>