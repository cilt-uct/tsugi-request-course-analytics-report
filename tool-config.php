<?php
// Configuration file - copy from tool-config_dist.php to tool-config.php
// and then edit.

if ((basename(__FILE__, '.php') != 'tool-config') && (file_exists('tool-config.php'))) {
    include 'tool-config.php';
    return;
}

# The configuration file - stores the paths to the scripts
$tool = array();
$tool['debug'] = FALSE;
$tool['active'] = TRUE; # if false will show coming soon page

// bo settings
$tool['csv_output_folder'] = '/data/bo/csv';
$tool['msg_template_folder'] = '/data/bo/templates/';
// classlist url
$tool['classliturl'] = 'https://srvubuclt002.uct.ac.za/d2l/api/course/classlist/';
$tool['coursedetailsurl'] = 'https://srvubuclt002.uct.ac.za/d2l/api/course/';
// middleware settings
$tool['middleware_username'] = '';
$tool['middleware_password'] = '';

// server details
$tool['bo_server_host'] = '';
$tool['server_username'] = '';
$tool['server_password'] = '';

# these sites are used for development - so ignore coming soon page
$tool['dev'] = [];

// reports
$reports = [
    [
        'id' => 'BO001', # KYCS bo report template
        'title' =>  'on-demand KYCS report',
        'description' => 'this is a short blurb for KYCS',
        'img' =>  'static/reports/uct-dass-logo.png',
        'bo_id' =>  'cuid_AfiAp2gwe2VOrc1APJ6uD9M',
        'bo_email' =>  'BO001_body.eml',
        'url' => addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('kycs-reports.php')) ),
        'active' =>  true,
        'time' => [
            ['id' => 'Semester One', 'value' => '1st Semester'],
            ['id' => 'Semester Two', 'value' => '2nd Semester'],
            ['id' => 'Full Year', 'value' => 'Full Year'],
        ],
        'kycsroles' => ['Lecturer', 'Lecturer Tutor', 'Owner', 'Tutor']
    ],
    [
        'id' => 'BO001B', # KYCS bo report template
        'title' =>  'on-demand KYCS report New',
        'img' =>  '/static/reports/kycs.png',
        'bo_id' =>  'cuid_AfiAp2gwe2VOrc1APJ6uD9M',
        'bo_email' =>  'BO001_body.eml',
        'url' => addSession('/kycsreports/index.php'),
        'active' =>  false,
        'time' => [
            ['id' => 'Semester One', 'value' => '1st Semester'],
            ['id' => 'Semester Two', 'value' => '2nd Semester'],
            ['id' => 'Full Year', 'value' => 'Full Year'],
        ],
        'roles' => ['Lecturer', 'Owner']
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
    ],
    [
        'id' => 'BO001B', # KYCS bo report template
        'title' =>  'on-demand KYCS report New',
        'img' =>  '/static/reports/kycs.png',
        'bo_id' =>  'cuid_AfiAp2gwe2VOrc1APJ6uD9M',
        'bo_email' =>  'BO001_body.eml',
        'url' => '/kycsreports/index.php',
        'active' =>  false,
        'time' => [
            ['id' => 'Semester One', 'value' => '1st Semester'],
            ['id' => 'Semester Two', 'value' => '2nd Semester'],
            ['id' => 'Full Year', 'value' => 'Full Year'],
        ],
        'roles' => ['Lecturer', 'Owner']
    ]
    ];