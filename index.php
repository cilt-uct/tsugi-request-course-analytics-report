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
$user_full_surname = $LAUNCH->ltiRawParameter('lis_person_name_family');
$recipients_data = [];
$docid = '';

$menu = false;

// get course code from middleware: use the following 42271 as example
if ($site_id == 6824) {
    $site_id = 42271;
}

$courseDetails = fetchWithBasicAuth($tool['coursesurl'] .'/'.$site_id, $tool['middleware_username'], $tool['middleware_password']);
$courseproviders = fetchWithBasicAuth($tool['coursesurl'] .'providers/'.$site_id, $tool['middleware_username'], $tool['middleware_password']);

$courseCode = explode('_', $courseDetails['data']['Code'])[0];
$year = $courseDetails['data']['Semester']['Code'];
// get results
$reports_data = $PDOX->allRowsDie("SELECT * FROM bo_reports_jobs WHERE course_id = {$site_id}");
$emails = array_column($reports_data, 'data');
// runnung report
$running_reports = $PDOX->allRowsDie("SELECT * FROM bo_reports_jobs WHERE course_id = {$site_id} and state != 'Completed' ");

// get results
$reports_data = $PDOX->allRowsDie("SELECT * FROM bo_reports_jobs WHERE course_id = {$site_id}");

if (str_contains($lms_info, 'sakai')) {
    // display any admin params needed here

    //get all recepients
    $recipients_data = $PDOX->allRowsDie("SELECT lti_user.user_id,lti_user.displayname, context_id, lti_user.email, JSON_UNQUOTE(ifnull(JSON_EXTRACT(lti_user.`json`,'$.sourcedId'), LOWER(SUBSTRING(lti_user.email, 1, LOCATE('@', lti_user.email) - 1)))) as eid, user_key FROM lti_user,lti_membership
    where lti_user.user_id=lti_membership.user_id and context_id={$CONTEXT->id} order by displayname");

} else if (str_contains($lms_info, 'desire2learn')){
    //get all recepients

    $fullurl = $tool['coursesurl'] . 'classlist/' . $site_id;
    $allrecipients = fetchWithBasicAuth($fullurl, $tool['middleware_username'], $tool['middleware_password']);

    foreach ($reports as $report) {
        if (isset($report['kycsroles']) || isset($report['bo_id'])) {
            $rolesToMatch = $report['kycsroles'];
            $docid = $report['bo_id'];
            break;
        }
    }

    // Filter users
    $filteredRecipients = array_filter($allrecipients['data'], function($user) use ($rolesToMatch) {
        return in_array($user['ClasslistRoleDisplayName'], $rolesToMatch) && !empty($user['Email']);
    });

    $recipients_data = json_encode(array_values($filteredRecipients));
}

$context = [
    'instructor' => $USER->instructor,
    'requesterid' => $USER->id,
    'requester_firstname' => $USER->firstname,
    'requester_lastname' => $user_full_surname,
    'requester_email' => $USER->email,
    'stylesheets' => [addSession('static/css/app.css'), addSession('static/css/bootstrap-select.min.css')],
    'scripts' =>    [addSession('static/js/multiselect.min.js'), addSession('static/js/bootstrap-select.min.js')],
    'reports' => $reports,
    'siteid' => $site_id,
    'allrecepients' => $recipients_data,
    'bo_id' => $docid,
    'year' => $year,
    'kycsformurl' => addSession(str_replace("\\","/",$CFG->getCurrentFileUrl('boreports/form.php'))),
    'past_reports' => $reports_data,
    'course_details_url' => $courseDetails,
    'course_code' => $courseCode,
    'course_providers' => $courseproviders['data'],
    'course_providers_data' => json_encode($courseproviders),
    'running_reports' => $running_reports,
    'running_reports_data' => json_encode($running_reports)
];

// echo '<pre>' . var_export($LAUNCH, true) . '</pre>';
// echo '<pre>' . var_export($context, true) . '</pre>';

// admin section
if ($USER->instructor){

    $OUTPUT->header();
    Template::view('templates/header.html', $context);

    $OUTPUT->bodyStart();
    $OUTPUT->topNav($menu);
    $OUTPUT->flashMessages();
    echo('<div class="container mt-5">
            <div class="row align-items-center">
                <div class="title col-md-6">
                    <h1 class="course-reports-heading">DASS Course Reports</h1>
                </div>
                <div class="col-md-6 text-right">
                    <a href="https://dass.uct.ac.za" target="_blank"><img src="static/reports/uct-dass-logo.png" alt="Logo" class="dass-logo img-fluid"></a>
                </div>
            </div>
        </div>');

    Template::view('templates/index.html', $context);

} else {
    $OUTPUT->flashMessages();
    echo("<p>You do not have access to view this page.</p>\n");
}

$OUTPUT->footerStart();

Template::view('templates/footer.html', $context);
$OUTPUT->footerEnd();

?>
