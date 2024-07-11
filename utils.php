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

    // Decode JSON response into an associative array
    $data = json_decode($response, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Decode Error: ' . json_last_error_msg());
    }

    return $data;
}

function trigger_kycs_report($remoteServer, $username, $password, $filepath){
    $port = 22;

    $remoteCsvDir = '/usr/local/src/bo-reports/ondemandcsv/';
    $remoteScriptPath = '/usr/local/src/bo-reports/ondemand.sh';

    // Establish SSH connection
    $connection = ssh2_connect($remoteServer, $port);
    if (!$connection) {
        die('Connection failed');
    }

    // Authenticate
    if (!ssh2_auth_password($connection, $username, $password)) {
        die('Authentication failed');
    }

    $remoteCsvPath = $remoteCsvDir . basename($filepath);

    // snd file over
    if (!ssh2_scp_send($connection, $filepath, $remoteCsvPath)) {
        die('File transfer failed');
    }

    $command = $remoteScriptPath . ' ' . escapeshellarg($remoteCsvPath) . ' 2>&1';
    $stream = ssh2_exec($connection, $command);

    if (!$stream) {
        die('Execution failed');
    }

    // Enable stream blocking and read the output
    stream_set_blocking($stream, true);
    $output = stream_get_contents($stream);
    $errorOutput = stream_get_contents($stream, 1024, SSH2_STREAM_STDERR);

    // Close the stream
    fclose($stream);

    // Output the result
    echo "Output:\n" . $output;
    if (!empty($errorOutput)) {
        echo "Error Output:\n" . $errorOutput;
    }

    // Close the connection
    ssh2_disconnect($connection);
}

function createCSV($filename, $data) {
    $file = fopen($filename, 'w');

    // Write headers
    fputcsv($file, ['Course Code', 'Email', 'First Name', 'Last Name', 'Year'], ';');

    // Write data
    foreach ($data as $row) {
        fputcsv($file, $row, ';');
    }

    fclose($file);
}