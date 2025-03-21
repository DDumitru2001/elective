<?php
require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$PAGE->requires->css('/mod/elective/elective.css');
$PAGE->requires->js('/mod/elective/elective.js');

$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$updated_course = optional_param_array('selected_course', [], PARAM_INT);

if (!$cm = get_coursemodule_from_id('elective', $id)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}

if (!$module = $DB->get_record('elective', array('id' => $cm->instance))) {
    print_error('invalidid', 'elective');
}
require_login($course, true, $cm);
$settingsnav = $PAGE->settingsnav;
$electivenode = $settingsnav->add(get_string('elective', 'mod_elective'), null, navigation_node::TYPE_CONTAINER);
$instanceId = $cm->id;
$PAGE->set_url('/mod/elective/allocateStudents.php', array('id' => $cm->id));
$PAGE->set_title(format_string($module->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_module::instance($cm->id));
$context = context_module::instance($cm->id);

mod_elective_extend_settings_navigation($settingsnav, $electivenode);
echo $OUTPUT->header();

$courses = $DB->get_records('course', array('visible' => 1), '', '*');
$preferences = $DB->get_records('elective_answer', null, '','courseid, userid');
//var_dump($courses);
var_dump($preferences);
foreach ($courses as $course)
{
    var_dump($course->fullname . ' Id ' . $course->id);
}
//var_dump($courses->fullname);




echo $OUTPUT->footer();
