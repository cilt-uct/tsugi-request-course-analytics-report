<?php
require_once('../../config.php');
include('../tool-config.php');
require_once "dao/BOReportsDAO.php";
require_once "../utils.php";

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;

$LAUNCH = LTIX::requireData();
$PDOX = LTIX::getConnection();
$p = $CFG->dbprefix;

use \BOReports\DAO\BOReportsDAO;

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$result = ['success' => 0, 'data' => 'Invalid request method. Please use POST.'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rawInput = file_get_contents('php://input');

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
        exit;
    }

    $input = json_decode($rawInput, true);

    $boreportsDAO = new BOReportsDAO($PDOX, $CFG->dbprefix);

    // Check action
    if (isset($input['action']) && $input['action'] === 'retry') {
        $result['success'] = $boreportsDAO->retryfailedboreport($input['id']) ? 1 : 0;
        $result['data'] = $result['success'] ? 'Retry triggered' : 'Error retrying';

    }
    else {

    $result['success'] = $boreportsDAO->runboreport(
        $input['site_id'],
        $LAUNCH->ltiRawParameter('context_title'),
        $input['year'],
        $input['providers'],
        $input['requester_id'],
        $input['firstname'],
        $input['lastname'],
        $input['to'],
        $input['report_type'],
        $input['bo_id']
        )? 1 : 0;

    $result['data'] = $result['success'] === 1 ? 'Inserted' : 'Error Inserting';
    }
}

echo json_encode($result);
exit;

?>
