<?php

namespace KYCSReports\DAO;

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
// Retrieve the launch data if present
$PDOX = LTIX::getConnection();
$p = $CFG->dbprefix;

error_reporting(E_ALL);
ini_set('display_errors', 1);

class KYCSReportsDAO {
    private $PDOX;
    private $p;

    public function __construct($PDOX, $p) {
        $this->PDOX = $PDOX;
        $this->p = $p;
    }

    // store run kycs reports
    public function runkycsreport($requester_id, $course_id, $year, $providers, $data, $fullname, $report_type) {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        if (!is_array($providers)) {
            $providers = explode(',', $providers); // Assume comma-separated if not an array
        }

        try {
            foreach ($providers as $provider_id) {
                // Trim whitespace around provider IDs
                $provider_id = trim($provider_id);

                $this->PDOX->queryDie("INSERT INTO {$this->p}reports_kycs_jobs
                    (requester_id, requester_name, course_id, term, provider_id, data, report_type, schedule_id, state, created_at)
                    VALUES (:requester_id, :fullname, :course_id, :term, :provid, :data, :type, '', 'Submitting', NOW())",
                    array(
                        ':requester_id' => $requester_id,
                        ':fullname' => $fullname,
                        ':course_id' => $course_id,
                        ':term' => $year,
                        ':provid' => $provider_id,
                        ':data' => $data,
                        ':type' => $report_type
                    ));
            }
            return TRUE;
        } catch (PDOException $e) {
            return FALSE;
        }
    }

}