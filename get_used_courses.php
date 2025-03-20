<?php
require_once(__DIR__.'/../../config.php');
require_login();

$quizid = required_param('quizid', PARAM_INT);

if (!$cm = get_coursemodule_from_id('elective', $quizid)) {
    print_error('invalidcoursemodule');
}

$usedCourses = [];
$questions = $DB->get_records_sql("SELECT courseids FROM {elective_question} WHERE quizid = :quizid", ['quizid' => $quizid]);

foreach ($questions as $question) {
    $courseids = strpos($question->courseids, ',') !== false ? explode(',', $question->courseids) : [$question->courseids];
    $usedCourses = array_merge($usedCourses, $courseids);
}

echo json_encode(array_unique($usedCourses));
