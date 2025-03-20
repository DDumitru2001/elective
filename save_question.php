<?php
require_once(__DIR__.'/../../config.php');
global $DB;
$id = required_param('id', PARAM_INT);
$quizid = required_param('quizid', PARAM_INT);
$questiontype = required_param('questiontype', PARAM_TEXT);
$courseids = required_param('courseids', PARAM_TEXT);
$questiontext = required_param('questiontext', PARAM_TEXT);
$maxanswers = optional_param('maxanswers', 1, PARAM_INT);
$electivenumber = optional_param('electivenumber', 1, PARAM_INT);

$record = new stdClass();
$record->quizid = $quizid;
$record->questiontype = $questiontype;
$record->courseids = $courseids;
$record->questiontext = $questiontext;
$record->maxanswers = $maxanswers;
$record->electivenumber = $electivenumber;

$DB->insert_record('elective_question', $record);

redirect(new moodle_url('/mod/elective/view.php', array('id' => $id)));
