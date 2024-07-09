<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
"drop table if exists {$CFG->dbprefix}reports_ama_classlist_setting",
"drop table if exists {$CFG->dbprefix}reports_kycs_jobs"
);

// The SQL to create the tables if they don't exist
// Allow for a user to generate multiple course_codes and email_to fields
// course_codes (type: text) and email_to (type: text)

//amathuba_classlist_setting: [id, courseid, ama_classlisturl, modified_by(name), password, updated_at]

$DATABASE_INSTALL = array(
    array(
        "{$CFG->dbprefix}reports_ama_classlist_setting",
        "CREATE TABLE IF NOT EXISTS `{$CFG->dbprefix}reports_ama_classlist_setting` (
            `id` INTEGER NOT NULL AUTO_INCREMENT,
            `course_id` INTEGER NOT NULL,
            `ama_classlisturl` VARCHAR(255) NOT NULL,
            `modified_by_id` INTEGER NOT NULL,
            `modified_by` VARCHAR(255) NOT NULL,
            `roles` VARCHAR(255) NOT NULL DEFAULT 109,
            `middleware_username` VARCHAR(255) NOT NULL,
            `middleware_password` VARCHAR(255) NOT NULL,
            `updated_at`      DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE(modified_by_id, course_id)
          ) ENGINE = InnoDB DEFAULT CHARSET=utf8"
    ),
    array(
        "{$CFG->dbprefix}reports_kycs_jobs",
        "CREATE TABLE IF NOT EXISTS `{$CFG->dbprefix}reports_kycs_jobs` (
         `id` INTEGER NOT NULL AUTO_INCREMENT,
         `course_id` INTEGER NOT NULL,
         `requester_id` INTEGER NOT NULL,
         `data` TEXT NOT NULL,
         `created_at`   DATETIME NOT NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    ),

);
// Called after a table has been created...
$DATABASE_POST_CREATE = function($table) {
    global $CFG, $PDOX;
    // create a admin user middleware, fall back if admin doesn't save their details
    if ( $table == "{$CFG->dbprefix}reports_ama_classlist_setting") {
        $sql= "INSERT INTO {$CFG->dbprefix}reports_ama_classlist_setting
        (course_id, ama_classlisturl, modified_by_id, modified_by, roles, middleware_username, middleware_password, updated_at)
        VALUES ( '0', 'https://srvubuclt002.uct.ac.za/d2l/api/course/classlist', '0', 'Admin', '110', '01482222', 'test', NOW())";
        error_log("Post-create: ".$sql);
        echo("Post-create: ".$sql."<br/>\n");
        $q = $PDOX->queryDie($sql);
    }

};