<?php
require_once(__DIR__ . '/../../config.php');
require_login();
require_sesskey();

$userid = required_param('userid', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);

global $DB;

$conditions = ['userid' => $userid, 'instance_id' => $instanceid];

try {
    $deleted = $DB->delete_records('elective_answer', $conditions);

    if ($deleted) {
        echo json_encode(['status' => 'success', 'message' => 'Electives deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No electives found to delete.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
