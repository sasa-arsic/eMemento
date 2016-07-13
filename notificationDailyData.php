<?php

    include_once('api.header.php');

    // deviceToken: 8bd8d38e71f87add4fca3e8f7d5ccb957b3967bf90427ab98b9671c0ac95c67d

    $stmt = _prepare("SELECT i.id, i.refreshInterval FROM installation i WHERE deviceToken = ?");

    if (!$stmt->bind_param('s', $_GET['deviceToken'])) {
        _die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
    }
    
    _execute($stmt);

    $res = _res($stmt);
    $stmt -> close();

    $max = isset($_GET['max']) ? intval($_GET['max']) : 3;

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        
        if($user['refreshInterval'] > 0) {

            $stmt = _prepare("INSERT INTO knowledge (time, installation, question) SELECT CURRENT_TIMESTAMP, ?, q.id FROM question q WHERE q.id NOT IN (SELECT k.question FROM knowledge k where installation != ?) ORDER BY RAND() LIMIT ?");

            if (!$stmt->bind_param('iii', $user['id'], $user['id'], $max)) {
                _die("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            _execute($stmt);
            $stmt -> close();

            /*
            apns($_GET['deviceToken'], array(
                'alert' => array(
                    'body' => 'Body 1',
                    'title' => 'Title 1'
                ),
                'category' => 'basic',
                'sound' => 'default'
            ));
            */
        }
    }

    include_once('api.footer.php');

?>