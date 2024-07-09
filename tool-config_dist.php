<?php
// Configuration file - copy from tool-config_dist.php to tool-config.php
// and then edit.

if ((basename(__FILE__, '.php') != 'tool-config') && (file_exists('tool-config.php'))) {
    include 'tool-config.php';
    return;
}

if ((basename(__FILE__, '.php') != 'tool-config') && (file_exists('../tool-config.php'))) {
    include '../tool-config.php';
    return;
}

# The configuration file - stores the paths to the scripts
$tool = array();
$tool['debug'] = FALSE;
$tool['active'] = TRUE; # if false will show coming soon page

$tool['csv_output_folder'] = 'outputfolder';
$tool['msg_template_folder'] = 'templatefolder';
// classlist url
$tool['classliturl'] = 'classliturl';
// middleware settings
$tool['middleware_username'] = 'username';
$tool['middleware_password'] = 'password';

// reports
$reports = [
    [
        'id' => 'BO001', # KYCS bo report template
        'title' =>  'on-demand KYCS report',
        'description' => 'this is a short blurb for KYCS',
        'img' =>  '/static/reports/uct-dass-logo.png',
        'bo_id' =>  'cuid_AfiAp2gwe2VOrc1APJ6uD9M',
        'bo_email' =>  'BO001_body.eml',
        'url' => addSession('/kycsreports/kycs-reports.php'),
        'active' =>  false,
        'time' => [
            ['id' => 'Semester One', 'value' => '1st Semester'],
            ['id' => 'Semester Two', 'value' => '2nd Semester'],
            ['id' => 'Full Year', 'value' => 'Full Year'],
        ],
        'roles' => ['Lecturer', 'Lecturer Tutor', 'Owner', 'Tutor']
    ],
    [
        'id' => 'BO001B', # KYCS bo report template
        'title' =>  'on-demand KYCS report New',
        'img' =>  '/static/reports/kycs.png',
        'bo_id' =>  'cuid_AfiAp2gwe2VOrc1APJ6uD9M',
        'bo_email' =>  'BO001_body.eml',
        'active' =>  false,
        'time' => [
            ['id' => 'Semester One', 'value' => '1st Semester'],
            ['id' => 'Semester Two', 'value' => '2nd Semester'],
            ['id' => 'Full Year', 'value' => 'Full Year'],
        ],
        'roles' => ['Lecturer', 'Owner']
    ]
    ];