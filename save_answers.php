<?php
require_once(__DIR__.'/../../config.php');
require_login();

$userid = $USER->id;
$quizid = required_param('id', PARAM_INT); // Quiz instance ID
$cm = get_coursemodule_from_id('elective', $quizid);

if (!$cm) {
    print_error('invalidcoursemodule');
}

$transaction = $DB->start_delegated_transaction();

try {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'question_') === 0) {
            $questionid = (int) str_replace('question_', '', $key);
            $quizid = $DB->get_field('elective_question', 'quizid', ['id' => $questionid]);
            $instanceid = $cm->instance;

            if (is_array($value)) {
                foreach ($value as $courseid) {
                    $record = new stdClass();
                    $record->userid = $userid;
                    $record->courseid = $courseid;
                    $record->coursefullname = $DB->get_field('course', 'fullname', ['id' => $courseid]);
                    $record->questionid = $questionid;
                    $record->quizid = $quizid;
                    $record->instance_id = $instanceid;

                    $DB->insert_record('elective_answer', $record);
                }
            } else {
                // Pentru single choice
                $record = new stdClass();
                $record->userid = $userid;
                $record->courseid = $value;
                $record->coursefullname = $DB->get_field('course', 'fullname', ['id' => $value]);
                $record->questionid = $questionid;
                $record->quizid = $quizid;
                $record->instance_id = $instanceid;

                $DB->insert_record('elective_answer', $record);
            }
        }
    }

    $transaction->allow_commit();
    redirect(new moodle_url('/mod/elective/view.php', ['id' => $quizid]), 'Answers submitted successfully', null, \core\output\notification::NOTIFY_SUCCESS);
} catch (Exception $e) {

    $transaction->rollback($e);
    print_error('There was a problem saving your answers.');
}

echo $OUTPUT->footer();
