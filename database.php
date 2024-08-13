<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
"drop table if exists {$CFG->dbprefix}reports_kycs_jobs"
);

// The SQL to create the tables if they don't exist
// Allow for a user to generate multiple course_codes and email_to fields
// course_codes (type: text) and email_to (type: text)

$DATABASE_INSTALL = array(
    array(
        "{$CFG->dbprefix}reports_kycs_jobs",
        "CREATE TABLE IF NOT EXISTS `{$CFG->dbprefix}reports_kycs_jobs` (
         `id` INTEGER NOT NULL AUTO_INCREMENT,
         `course_id` INTEGER NOT NULL,
         `title` VARCHAR(255) NOT NULL,
         `term` VARCHAR(30) NOT NULL,
         `provider_id` VARCHAR(30) NOT NULL,
         `requester_id` INTEGER NOT NULL,
         `firstname` VARCHAR(255) NOT NULL,
         `lastname` VARCHAR(255) NOT NULL,
         `data` TEXT NOT NULL,
         `report_type` VARCHAR(255) NOT NULL,
         `document_id` VARCHAR(255) NOT NULL,
         `schedule_id` VARCHAR(30) NULL,
         `state` VARCHAR(255) NOT NULL,
         `created_at`   DATETIME NOT NULL,
         `modified_at`   DATETIME NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    ),

);