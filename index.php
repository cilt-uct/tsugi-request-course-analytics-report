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

$handledRoster = LTIX::populateRoster(false, true);

// check lms ext here
$lms_info = $LAUNCH->ltiRawParameter('tool_consumer_info_product_family_code');

$receipients_data = [];
$docid = '';

// get results
$reports_data = $PDOX->allRowsDie("SELECT * FROM reports_kycs_jobs WHERE course_id = {$site_id}");
$emails = array_column($reports_data, 'data');

$menu = false;

// get course coude from middleware: use the following 42271 as example
$site_id = 42271;
$courseDetails = fetchWithBasicAuth($tool['coursesurl'] .'/'.$site_id, $tool['middleware_username'], $tool['middleware_password']);
$courseproviders = fetchWithBasicAuth($tool['coursesurl'] .'providers/'.$site_id, $tool['middleware_username'], $tool['middleware_password']);
// $allcourses = fetchWithBasicAuth($tool['allcourses'], $tool['middleware_username'], $tool['middleware_password']);
$courseCode = explode('_', $courseDetails['data']['Code'])[0];

// get results
$reports_data = $PDOX->allRowsDie("SELECT * FROM reports_kycs_jobs WHERE course_id = {$site_id}");

if (str_contains($lms_info, 'sakai')) {
    // display any admin params needed here

    //get all recepients
    $receipients_data = $PDOX->allRowsDie("SELECT lti_user.user_id,lti_user.displayname, context_id, lti_user.email, JSON_UNQUOTE(ifnull(JSON_EXTRACT(lti_user.`json`,'$.sourcedId'), LOWER(SUBSTRING(lti_user.email, 1, LOCATE('@', lti_user.email) - 1)))) as eid, user_key FROM lti_user,lti_membership
    where lti_user.user_id=lti_membership.user_id and context_id={$CONTEXT->id} order by displayname");

} else if (str_contains($lms_info, 'desire2learn')){
    //get all recepients

    $fullurl = $tool['coursesurl'] . 'classlist/' . $site_id;

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

$context = [
    'instructor' => $USER->instructor,
    'requesterid' => $USER->id,
    'requester_fullname' => $USER->firstname.' '.$USER->lastname,
    'instructor_email' => $USER->email,
    'stylesheets' => [addSession('static/css/app.css'), addSession('static/css/bootstrap-select.min.css')],
    'scripts' =>    [addSession('static/js/multiselect.min.js'), addSession('static/js/bootstrap-select.min.js')],
    'reports' => $reports,
    'siteid' => $site_id,
    'allrecepients' => $receipients_data,
    'bo_id' => $docid,
    'kycsformurl' => addSession(str_replace("\\","/",$CFG->getCurrentFileUrl('kycsreports/form.php'))),
    'past_reports' => $reports_data,
    'course_details_url' => $courseDetails,
    'course_code' => $courseCode,
    'course_providers' => json_encode($courseproviders)
];

// admin section
if ($USER->instructor){

    $OUTPUT->header();
    Template::view('templates/header.html', $context);

    $OUTPUT->bodyStart();
    $OUTPUT->topNav($menu);
    $OUTPUT->flashMessages();
    echo("<p>Welcome to course analytics, please select a report that you would like to generate.</p>\n");

    Template::view('templates/index.html', $context);

} else {
    $OUTPUT->flashMessages();
    echo("<p>You do not have access to view this page.</p>\n");
}


$OUTPUT->footerStart();

Template::view('templates/footer.html', $context);
$OUTPUT->footerEnd();

?>
