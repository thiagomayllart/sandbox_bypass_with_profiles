<?php

$logFilePath = 'unique_client_ips.log';
$clientIP = $_SERVER['REMOTE_ADDR'];
$ipFound = false;

// Open the log file for reading
if ($file = fopen($logFilePath, "r")) {
    while (!feof($file) && !$ipFound) {
        $loggedIP = trim(fgets($file));
        if ($loggedIP == $clientIP) {
            $ipFound = true;
        }
    }
    fclose($file);
}

echo $ipFound ? 'true' : 'false';

?>

