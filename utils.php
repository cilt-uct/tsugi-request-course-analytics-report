<?php
// require_once('../config.php');
include 'tool-config_dist.php';

// fetch data from middleware using basic auth
function fetchWithBasicAuth($url, $username, $password) {
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($username . ':' . $password)
    ]);

    // Execute and get the response
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Error: ' . $error);
    }

    curl_close($ch);

    // Output the raw response for debugging

    // Decode JSON response into an associative array
    $data = json_decode($response, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Decode Error: ' . json_last_error_msg());
    }

    return $data;
}

// send csv to server
function trigger_kycs_report($remoteServer, $username, $password, $file){
    $port = 22;

    $remoteCsvDir = '/usr/local/src/bo-reports/ondemandcsv/';

    $remoteFile = $remoteCsvDir . basename($file);

    // Establish SSH connection
    $connection = ssh2_connect($remoteServer, $port);
    if (!$connection) {
        die('Connection failed');
    }

    // Authenticate
    if (!ssh2_auth_password($connection, $username, $password)) {
        die('Authentication failed');
    }

    $remoteCsvPath = $remoteCsvDir . basename($file);

    // send file over
    if (!ssh2_scp_send($connection, $file, $remoteCsvPath)) {
        die('File transfer failed');
    }

    $escapedCsvPath = escapeshellarg($remoteFile);
    $command = "$escapedCsvPath";
    $stream = ssh2_exec($connection, $command);

    if (!$stream) {
        die('Execution failed');
    }

    stream_set_blocking($stream, true);

    // Read the output
    $output = stream_get_contents($stream);

    // Close the streams
    fclose($stream);

}

function createCSV($filename, $data) {
    $file = fopen($filename, 'w');

    // Write headers
    fputcsv($file, ['Course Code', 'Email', 'First Name', 'Last Name', 'Year', 'bo_id', 'Site Title', 'Site Id'], ';');

    // Write data
    foreach ($data as $row) {
        fputcsv($file, $row, ';');
    }

    fclose($file);
}