<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
"drop table if exists {$CFG->dbprefix}kycs_reports_on_demand"
);

// The SQL to create the tables if they don't exist
// Allow for a user to generate multiple course_codes and email_to fields
// course_codes (type: text) and email_to (type: text)
$DATABASE_INSTALL = array(
array( "{$CFG->dbprefix}kycs_reports_on_demand",
"create table {$CFG->dbprefix}kycs_reports_on_demand (
    id              INTEGER NOT NULL AUTO_INCREMENT,
    requester_id    INTEGER NOT NULL,
    course_codes    VARCHAR(255) NOT NULL,
    email_to        VARCHAR(255) NOT NULL,
    created_at      DATETIME NOT NULL,

    PRIMARY KEY (id),
    UNIQUE(user_id)
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);

