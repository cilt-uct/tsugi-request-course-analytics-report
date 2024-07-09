<?php
require_once('../../config.php');
include('../tool-config.php');

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;

$LAUNCH = LTIX::requireData();
$PDOX = LTIX::getConnection();
$p = $CFG->dbprefix;

$site_id = $LAUNCH->ltiRawParameter('context_id','none');
require_once "dao/KYCSReportsDAO.php";
use \KYCSReports\DAO\KYCSReportsDAO;
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rawInput = file_get_contents('php://input');
    // Decode the JSON input
    $input = json_decode($rawInput, true);

    error_log(print_r($input, true));
    error_log($rawInput);

    // Ensure data was decoded correctly
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
        exit;
    }

    $form_id = htmlspecialchars($input['form_id'] ?? '');
    $requester_id = htmlspecialchars($input['requester_id'] ?? '');
    //$data = isset($input['to']) && is_array($input['to']) ? array_map('htmlspecialchars', $input['to']) : [];
    $site_id = htmlspecialchars($input['site_id'] ?? '');
    $semester = htmlspecialchars($input['semester'] ?? '');
    $data = isset($input['to']) && is_array($input['to']) ? array_map(function($recipient) {
        return [
            'email' => htmlspecialchars($recipient['email']),
            'fullname' => htmlspecialchars($recipient['fullname'])
        ];
    }, $input['to']) : [];

    var_dump($form_id . ' '. $requester_id . ' '. json_encode($data) . ' '. $semester);

    if ($form_id === 'kycs_generate_form') {
        $jsonData = json_encode($data);
        $kycsreportsDAO = new KYCSReportsDAO($PDOX, $CFG->dbprefix);
        $response = $kycsreportsDAO->runkycsreport($requester_id, $site_id, $jsonData);
        var_dump($response);
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid form submission']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

    // save kycs settings
    //  move to separete file
    function trigger_kycs_report($data){
        // get below from auth.pl
        $remoteServer = '';
        $port = 22;
        // $username = '';
        // $password = '.';
        $username = '';
        $password = '';
        // $pythonFilePath = '/usr/local/src/bo-reports/run.py';
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

        // Serialize data to JSON
        $jsonData = json_encode($data);

        // Escape the JSON data for the shell command
        $escapedJsonData = escapeshellarg($jsonData);

        // Execute the Python script
        // $command = 'python3 ' . $pythonFilePath . ' 2>&1';
        //$command = 'python3 ' . $pythonFilePath . ' ' . escapeshellarg('function_one') . ' 2>&1'; // Redirect stderr to stdout
        $command = $remoteScriptPath . ' ' . escapeshellarg('ondemand_task') . ' 2>&1';
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
?>
