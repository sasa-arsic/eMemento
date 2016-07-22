<?php

    include_once('api.header.php');

    _requireFields('get', array('deviceToken'));

    $max = isset($_GET['max']) ? intval($_GET['max']) : 3;
    $quizz = isset($_GET['quizz']) ? intval($_GET['quizz']) : 1;

    $stmt = _prepare("SELECT i.id, i.refreshInterval FROM installation i WHERE deviceToken = ?");

    if (!@$stmt->bind_param('s', $_GET['deviceToken'])) {
        _die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    
    _execute($stmt);

    $res = _res($stmt);
    $stmt -> close();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        
        if($user['refreshInterval'] > 0) {

            $stmt = _prepare("INSERT INTO knowledge (time, installation, question) 
                SELECT CURRENT_TIMESTAMP, ?, q.id FROM question q 
                WHERE q.quizz = ? AND q.id NOT IN (SELECT k.question FROM knowledge k where k.installation = ?) 
                ORDER BY RAND() LIMIT ?");

            if (!@$stmt->bind_param('iiii', $user['id'], $quizz, $user['id'], $max)) {
                _die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            _execute($stmt);
            $inserted = $stmt->affected_rows;
            $stmt -> close();

            if($inserted > 0) {
                
                $stmt = _prepare("SELECT k.id, q.label, q.answer FROM knowledge k 
                                LEFT JOIN question q ON q.id = k.question
                                WHERE k.installation = ? AND q.quizz = ? 
                                ORDER BY time DESC LIMIT ?");

                if (!@$stmt->bind_param('iii', $user['id'], $quizz, $max)) {
                    _die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
                }
                
                _execute($stmt);

                $res = _res($stmt);

                $data = array();
                while($row = $res->fetch_assoc()) {
                    $data[] = array(
                        'id' => $row['id'],
                        'key' => $row['label'],
                        'value' => $row['answer']
                    );
                }

                $payload = array(
                    'aps' => array(
                        'alert' => array(
                            'body' => 'New capitals to learn',
                            'title' => 'Daily Learning'
                        ),
                        'category' => 'training',
                        'sound' => 'default'
                    ),
                    'data' => $data
                );

                if ( DVPT ) {
                    $__return = $payload;
                }
                
                apns($_GET['deviceToken'], $payload);
            }
        }
    }

    include_once('api.footer.php');

?>