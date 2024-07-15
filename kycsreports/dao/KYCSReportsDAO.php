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
    public function runkycsreport($requester_id, $course_id, $data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
            try {
                $this->PDOX->queryDie("INSERT INTO {$this->p}reports_kycs_jobs
                (requester_id, course_id, data, state, created_at)
                VALUES (:requester_id, :course_id, :data, '', NOW())",
                    array(':requester_id' => $requester_id, ':course_id' => $course_id, 'data' => $data));

                return TRUE;
            } catch (PDOException $e) {
                return FALSE;
            }
        }

}