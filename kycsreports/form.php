<?php
require_once('../../config.php');
include('../tool-config.php');
require_once "dao/KYCSReportsDAO.php";
require_once "../utils.php";

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;

$LAUNCH = LTIX::requireData();
$PDOX = LTIX::getConnection();
$p = $CFG->dbprefix;

use \KYCSReports\DAO\KYCSReportsDAO;

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

    $kycsreportsDAO = new KYCSReportsDAO($PDOX, $CFG->dbprefix);

    $result['success'] = $kycsreportsDAO->runkycsreport(
        $input['requester_id'],
        $input['site_id'],
        $input['year'],
        $input['providers'],
        $input['to'],
        $input['requester_fullname'],
        $input['report_type'])? 1 : 0;;

    $result['data'] = $result['success'] === 1 ? 'Inserted' : 'Error Inserting';

    // csv data, loop through everyon here
    if ($result['success'] === 1) {

        $dataForCSV = [];
        $courseCode = '';

        foreach ($input['to'] as $recipient) {

            // used for testing with course with providers
            $site_id = $input['site_id'];
            $courseDetails = fetchWithBasicAuth($tool['coursesurl'] .'/'.$site_id, $tool['middleware_username'], $tool['middleware_password']);

            //$courseDetails = fetchWithBasicAuth($tool['coursedetailsurl'] .'/'.$input['site_id'], $tool['middleware_username'], $tool['middleware_password']);

            $courseCode = explode('_', $courseDetails['data']['Code'])[0];
            $year = $courseDetails['data']['Semester']['Code'];

            $providersData = [];

            // Group users by provider
                foreach ($input['providers'] as $provider) {
                    $providersData[$provider][] = [
                        'Course Code' => $provider,
                        'Email' => $recipient['email'],
                        'First Name' => $recipient['firstname'],
                        'Last Name' => $recipient['lastname'],
                        'Year' => $year,
                        'bo_id' => $input['bo_id'],
                        'Site Title' => $LAUNCH->ltiRawParameter('context_title'),
                        'Site Id' => $site_id,
                    ];
                }
            }

        $filename = '';

        // Save each provider's data to a CSV file
        foreach ($providersData as $provider => $dataForCSV) {
            $filename = "../csv/{$provider}_" . date('Y-m-d_H-i-s') . ".csv";
            createCSV($filename, $dataForCSV);

            if (file_exists($filename)) {

                trigger_kycs_report($tool['bo_server_host'], $tool['server_username'], $tool['server_password'], $filename);
            }
        }

    }

}
echo json_encode($result);
exit;

?>
