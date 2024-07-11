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

$handledRoster = LTIX::populateRoster(false, true);

// check lms ext here
$lms_info = $LAUNCH->ltiRawParameter('tool_consumer_info_product_family_code');
$menu = false;
?>

<?php
$context = [
    'instructor' => $USER->instructor,
    // add styles
    'stylesheet' => addSession('static/css/app.css'),
    'reports' => $reports,
    'kycsurl' => addSession('kycs-reports.php')
];

// admin section
if ($USER->instructor ){
    $OUTPUT->header();
    Template::view('templates/header.html', $context);

    $OUTPUT->bodyStart();
    $OUTPUT->topNav($menu);
    $OUTPUT->flashMessages();
    echo("<p>Welcome to course analytics, please select a report that you would like to generate.</p>\n");

    Template::view('templates/index.html', $context);
}
?>

