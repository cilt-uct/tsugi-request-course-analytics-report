<?php
require_once('../config.php');
include 'tool-config_dist.php';
include 'src/Template.php';

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Roster;


// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

$site_id = $LAUNCH->ltiRawParameter('context_id','none');
// $EID = $LAUNCH->ltiRawParameter('ext_d2l_username', $LAUNCH->ltiRawParameter('lis_person_sourcedid', $LAUNCH->ltiRawParameter('ext_sakai_eid', $USER->id)));
// lis_course_offering_sourcedid
$course_offering =  $LAUNCH->ltiRawParameter('lis_course_offering_sourcedid', 'none');
// var_dump($course_offering);
// check lms ext here
$lms_info = $LAUNCH->ltiRawParameter('tool_consumer_info_product_family_code');
$receipients_data = [];
$allreceipients = [];
// move auth to another file
function fetchWithBasicAuth($url, $userename, $password) {
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($userename . ':' . $password)
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

function filterByRole($receipients){

    // save this to database, for the admin, which roles,
    $desiredRoleIds = [110];
    // Filter function
    $filteredData = array_filter($receipients, function($item) use ($desiredRoleIds) {
        return isset($item['ClasslistRoleDisplayName']) && in_array($item['ClasslistRoleDisplayName'], $desiredRoleIds);
    });

    // Reset array keys
    return $filteredData;
}

if (str_contains($lms_info, 'sakai')) {
    // display any admin params needed here

    //get all recepients
    $receipients_data = $PDOX->allRowsDie("SELECT lti_user.user_id,lti_user.displayname, context_id, lti_user.email, JSON_UNQUOTE(ifnull(JSON_EXTRACT(lti_user.`json`,'$.sourcedId'), LOWER(SUBSTRING(lti_user.email, 1, LOCATE('@', lti_user.email) - 1)))) as eid, user_key FROM lti_user,lti_membership
    where lti_user.user_id=lti_membership.user_id and context_id={$CONTEXT->id} order by displayname");

} else if (str_contains($lms_info, 'desire2learn')){
    //get all recepients
    $query = "SELECT * FROM {$p}reports_ama_classlist_setting WHERE course_id = :course_id;";

    $arr = array(':course_id' => $site_id);
    $row = $PDOX->rowDie($query, $arr);
    $fullurl = $tool['classliturl'] . '' . $site_id;
    //var_dump($fullurl);

    //$allreceipients = fetchWithBasicAuth('https://srvubuclt002.uct.ac.za/d2l/api/course/classlist/6824');

    // $allreceipients = fetchWithBasicAuth($fullurl, $tool['middleware_username'], $tool['middleware_password']);
    $allreceipients = fetchWithBasicAuth($fullurl, $row['middleware_username'], $row['middleware_password']);


    // var_dump($allreceipients);
    foreach ($reports as $report) {
        if (isset($report['kycsroles'])) {

            $reportRoles = implode(', ', $report['kycsroles']);
            // Print report roles
            // echo "Report roles: " . implode(', ', $report['kycsroles']) . "\n";

            $filteredRecipients = array_filter($allreceipients, function($recipient) use ($report) {
                // Print recipient role for debugging
                return isset($recipient['ClasslistRoleDisplayName']) && in_array($recipient['ClasslistRoleDisplayName'], $reportRoles);
            });

            // // Print filtered recipients
            // echo "Filtered Recipients:\n";
            // foreach ($filteredRecipients as $recipient) {
            //     echo "- " . $recipient['DisplayName'] . " (" . $recipient['ClasslistRoleDisplayName'] . ")\n";
            // }
        }
    }
    //var_dump($filteredRecipients);die();
    $receipients_data = $filteredRecipients;
    //$receipients_data = filterByRole($allreceipients['data']);


}
// var_dump($filteredRecipients);
// $OUTPUT->header();
// $OUTPUT->bodyStart();
// $OUTPUT->flashMessages();
// echo("<h1>on-demand KYCS report</h1>\n");
// $OUTPUT->welcomeUserCourse();
// $css = $CFG->getCurrentFileUrl('../css/app.css');

$menu = false;
// $settings_data = $PDOX->allRowsDie("SELECT * FROM reports_ama_classlist_setting
//     where course_id = $site_id");

$context = [
    'instructor' => $USER->instructor,
    'requesterid' => $USER->id,
    'stylesheet' => addSession('static/css/app.css'),
    'script' =>    addSession('static/js/multiselect.min.js'),
    'reports' => $reports,
    'siteid' => $site_id,
    'allrecepients' => $allreceipients,
    'kycsformurl' =>addSession(str_replace("\\","/",$CFG->getCurrentFileUrl('kycsreports/form.php'))),

];

// var_dump($receipients_data);die();
$OUTPUT->header();
Template::view('templates/header.html', $context);

$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
echo("<h1>on-demand KYCS report</h1>\n");
$OUTPUT->topNav($menu);
$OUTPUT->welcomeUserCourse();
Template::view('templates/kycs-reports.html', $context);

$OUTPUT->footerStart();

Template::view('templates/kycs-reports-footer.html', $context);
$OUTPUT->footerEnd();

?>

