<?php

$socket = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);

if (!$socket) {
    echo "$errstr ($errno)<br />\n";
} else {
    while ($conn = stream_socket_accept($socket)) {
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
    }
    fclose($socket);
}