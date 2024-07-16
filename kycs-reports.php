<?php
require_once('../config.php');
include 'tool-config_dist.php';
include 'src/Template.php';
require_once("utils.php");

use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Roster;


// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

$site_id = $LAUNCH->ltiRawParameter('context_id','none');

$course_offering =  $LAUNCH->ltiRawParameter('lis_course_offering_sourcedid', 'none');

// check lms ext here
$lms_info = $LAUNCH->ltiRawParameter('tool_consumer_info_product_family_code');
$receipients_data = [];
$docid = '';

// get results
$reports_data = $PDOX->allRowsDie("SELECT * FROM reports_kycs_jobs WHERE course_id = {$site_id}");
$emails = array_column($reports_data, 'data');

if (str_contains($lms_info, 'sakai')) {
    // display any admin params needed here

    //get all recepients
    $receipients_data = $PDOX->allRowsDie("SELECT lti_user.user_id,lti_user.displayname, context_id, lti_user.email, JSON_UNQUOTE(ifnull(JSON_EXTRACT(lti_user.`json`,'$.sourcedId'), LOWER(SUBSTRING(lti_user.email, 1, LOCATE('@', lti_user.email) - 1)))) as eid, user_key FROM lti_user,lti_membership
    where lti_user.user_id=lti_membership.user_id and context_id={$CONTEXT->id} order by displayname");

} else if (str_contains($lms_info, 'desire2learn')){
    //get all recepients

    $fullurl = $tool['classliturl'] . '' . $site_id;

    $allreceipients = fetchWithBasicAuth($fullurl, $tool['middleware_username'], $tool['middleware_password']);

    foreach ($reports as $report) {
        if (isset($report['kycsroles']) || isset($report['bo_id'])) {
            $rolesToMatch = $report['kycsroles'];
            $docid = $report['bo_id'];
            break;
        }
    }

    // Filter users
    $filteredRecipients = array_filter($allreceipients['data'], function($user) use ($rolesToMatch) {
        return in_array($user['ClasslistRoleDisplayName'], $rolesToMatch) && !empty($user['Email']);
    });

    $receipients_data = json_encode(array_values($filteredRecipients));

}

$menu = false;

$context = [
    'instructor' => $USER->instructor,
    'requesterid' => $USER->id,
    'stylesheet' => addSession('static/css/app.css'),
    'script' =>    addSession('static/js/multiselect.min.js'),
    'reports' => $reports,
    'siteid' => $site_id,
    'allrecepients' => $receipients_data,
    'bo_id' => $docid,
    'kycsformurl' => addSession(str_replace("\\","/",$CFG->getCurrentFileUrl('kycsreports/form.php'))),
    'past_reports' => $reports_data
];

$OUTPUT->header();
Template::view('templates/header.html', $context);

$OUTPUT->bodyStart();
$OUTPUT->flashMessages();
echo("<h1>on-demand KYCS report</h1>\n");
$OUTPUT->topNav($menu);
$OUTPUT->welcomeUserCourse();
Template::view('templates/kycs-reports.html', $context);

if (count($reports_data) > 0) {
    Template::view('templates/kycs-past-reports.html', $context);
}


$OUTPUT->footerStart();

Template::view('templates/kycs-reports-footer.html', $context);
$OUTPUT->footerEnd();

?>

