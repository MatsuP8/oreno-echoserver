<?php
declare(ticks=1);

$socket = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);

pcntl_signal(SIGINT, sig_handler($socket));

stream_set_blocking($socket, false);

if (!$socket) {
    echo "$errstr ($errno)<br />\n";
} else {
    $conns = [];

    while (true) {
        $read = [$socket];
        $write = null;
        $except = null;
        $result = stream_select($read, $write, $except, 0);

        if ($result) {
            $conn = stream_socket_accept($socket);

            if (!$conn) {
                echo "$errstr ($errno)<br />\n";
            } else {
                stream_set_blocking($conn, false);
                $conns[] = $conn;
                echo "here is non blocking echo server!";
            }
        }

        foreach ($conns as $i => $conn) {
            $read = fread($conn, 8192);

            if (!$read) {
                continue;
            } else {
                echo "message receive: $read";
                if (rtrim($read, "\n\r") === 'quit') {
                    fclose($conn);
                    unset($conns[$i]);
                    break;
                }
                $res = "response: $read";
                fwrite($conn, $res, strlen($res));
            }
        }
    }
}

function sig_handler($socket)
{
    return function ($signo) use ($socket) {
        fclose($socket);
        exit(0);
    };
}