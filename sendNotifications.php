<?php

$deviceToken = '8bd8d38e71f87add4fca3e8f7d5ccb957b3967bf90427ab98b9671c0ac95c67d';







$servername = "fljf.vps.infomaniak.com";
$username = "fljf_sasa_arsic";
$password = "welcome16";
$dbname = "fljf_applewatch";



try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT Title, Subtitle FROM Notification ORDER BY RAND() LIMIT 3"); 
    $stmt->execute();

    // set the resulting array to associative
    $row = $stmt->fetch(PDO::FETCH_ASSOC); 
    $countryname = $row["Title"]; //perfect
    $countrycapital = $row["Subtitle"]; //perfect

   //echo $row = $stmt->fetchColumn();
    $message = $countryname . "-" . $countrycapital;


    


}
catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;





// call the method
sendNotificationToPhone($deviceToken, $message);



function sendNotificationToPhone($deviceToken, $message) {

$arr = explode("-", $message, 2);
$firstMessageElement = $arr[0];
$secondMessageElement =$arr[1];


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
        'alert' => 'Daily learning !',
        'countryName' => $firstMessageElement,
        'countryCapital' => $secondMessageElement,
        'category' => 'LearningInvitation',
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