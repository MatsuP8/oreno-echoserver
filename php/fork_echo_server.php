<?php

$socket = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);

if (!$socket) {
    echo "$errstr ($errno)<br />\n";
} else {
    while ($conn = stream_socket_accept($socket)) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('fork できません');
        } elseif ($pid) {
            echo $pid;
        } else {
            echo "client connected.\n";
            while ($read = fread($conn, 8192)) {
                echo "message receive: $read";

                if (rtrim($read, "\n\r") === 'quit') {
                    break;
                }

                $res = "response: $read";
                fwrite($conn, $res, strlen($res));
            }
            fclose($conn);
            exit(0);
        }
    }
    fclose($socket);
}