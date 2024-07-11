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


    public function getclass_list_settings($classlisturl, $username, $password, $roles, $course_id, $modified_by_id, $modified_by) {
        $sql = "INSERT INTO {$this->p}reports_ama_classlist_setting
                    (ama_classlisturl, middleware_username, middleware_password, roles, course_id, modified_by_id, modified_by, updated_at)
                VALUES
                    (:classlisturl, :middleware_username, :middleware_password, :roles, :course_id, :modified_by_id, :modified_by, NOW())
                ON DUPLICATE KEY UPDATE
                    ama_classlisturl = VALUES(ama_classlisturl),
                    middleware_username = VALUES(middleware_username),
                    middleware_password = VALUES(middleware_password),
                    roles = VALUES(roles),
                    modified_by_id = VALUES(modified_by_id),
                    modified_by = VALUES(modified_by),
                    updated_at = NOW()";


        $roles_string = implode(',', $roles);
        //var_dump($rolesJson); // Debugging

        $stmt = $this->PDOX->prepare($sql);

        $stmt->bindParam(':classlisturl', $classlisturl);
        $stmt->bindParam(':middleware_username', $username);
        $stmt->bindParam(':middleware_password', $password);
        $stmt->bindParam(':roles', $roles_string);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':modified_by_id', $modified_by_id);
        $stmt->bindParam(':modified_by', $modified_by);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Settings have been updated'];
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log('SQL error: ' . json_encode($errorInfo));
            return ['status' => 'error', 'message' => 'Database error: ' . $errorInfo[2]];
        }
    }

    // store run kycs reports
    public function runkycsreport($requester_id, $course_id, $data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
            try {
                $this->PDOX->queryDie("INSERT INTO {$this->p}reports_kycs_jobs
                (requester_id, course_id, data, created_at)
                VALUES (:requester_id, :course_id, :data, NOW())",
                    array(':requester_id' => $requester_id, ':course_id' => $course_id, 'data' => $data));

                return TRUE;
            } catch (PDOException $e) {
                return FALSE;
            }
        }

}