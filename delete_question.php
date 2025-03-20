<?php
require_once(__DIR__.'/../../config.php');
require_login();

$questionid = required_param('questionid', PARAM_INT); // ID-ul întrebării

if (!$question = $DB->get_record('elective_question', ['id' => $questionid])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid question ID.']);
    die();
}

$context = context_module::instance($question->quizid);
require_capability('mod/elective:addinstance', $context);

$transaction = $DB->start_delegated_transaction();
try {
    $DB->delete_records('elective_answer', ['questionid' => $questionid]);

    $DB->delete_records('elective_question', ['id' => $questionid]);

    $remaining_questions = $DB->get_records_sql(
        "SELECT id FROM {elective_question} WHERE quizid = :quizid ORDER BY electivenumber ASC",
        ['quizid' => $question->quizid]
    );

    $newNumber = 1;
    foreach ($remaining_questions as $q) {
        $DB->update_record('elective_question', (object)[
            'id' => $q->id,
            'electivenumber' => $newNumber
        ]);
        $newNumber++;
    }

    $transaction->allow_commit();

    echo json_encode(['status' => 'success', 'message' => 'Question and associated answers deleted.']);

} catch (Exception $e) {
    $transaction->rollback($e);
    echo json_encode(['status' => 'error', 'message' => 'There was a problem deleting the question and its answers.']);
    die();
}
