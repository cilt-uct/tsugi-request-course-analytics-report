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
    public function runkycsreport($course_id, $title, $term, $providers, $requester_id, $firstname, $lastname, $data, $report_type, $docid) {
        // keep to data input['to] as array for to handle multiple emails and names

        // print_r('Course ID: ' .$course_id. 'Term: ' .$term. 'proviers: ' .$providers. 'RequesterID: '.$requester_id. 'Requester: ' .$fullname. 'Data: ' .$data. 'Report type: ' .$report_type. 'Doc ID: ' .$docid);
        // die();
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
                    (course_id, title, term, provider_id, requester_id, firstname, lastname, data, report_type, document_id, schedule_id, state, created_at)
                    VALUES (:course_id, :title, :term, :prov_id, :requester_id, :firstname, :lastname, :data, :type, :doc_id, '', :state, NOW())",
                    array(
                        ':course_id' => $course_id,
                        ':title' => $title,
                        ':term' => $term,
                        ':prov_id' => $provider_id,
                        ':requester_id' => $requester_id,
                        ':firstname' => $firstname,
                        ':lastname' => $lastname,
                        ':data' => $data,
                        ':type' => $report_type,
                        ':doc_id' => $docid,
                        'state' => 'Submitting'
                    ));
            }
            return TRUE;
        } catch (PDOException $e) {
            return FALSE;
        }
    }

}