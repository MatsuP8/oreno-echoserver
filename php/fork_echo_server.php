<?php
declare(ticks=1);

$socket = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);

pcntl_signal(SIGINT, sig_handler($socket));

if (!$socket) {
    echo "$errstr ($errno)<br />\n";
} else {
    $pids = [];
    while (true) {
        $conn = @stream_socket_accept($socket, 5);
        if ($conn) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('fork できません');
            } elseif ($pid) {
                $pids[] = $pid;
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
        } else {
            foreach ($pids as $i => $pid) {
                $result = pcntl_waitpid($pid, $status, WNOHANG);
                if ($result > 0 && pcntl_wifexited($status)) {
                    unset($pids[$i]);
                }
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