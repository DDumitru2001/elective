<?php
require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/lib/phpspreadsheet/vendor/autoload.php'); // Include PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$PAGE->requires->css('/mod/elective/elective.css');
$PAGE->requires->js('/mod/elective/elective.js');

function get_course_options_for_question($questionid, $DB) {
    $sql = "
        SELECT c.id, c.fullname
        FROM {elective_question} eq
        JOIN {course} c ON FIND_IN_SET(c.id, eq.courseids)
        WHERE eq.id = :questionid
    ";
    $params = ['questionid' => $questionid];
    $courses = $DB->get_records_sql($sql, $params);

    return $courses;
}

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
$PAGE->set_url('/mod/elective/answers.php', array('id' => $cm->id));
$PAGE->set_title(format_string($module->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_module::instance($cm->id));
$context = context_module::instance($cm->id);

mod_elective_extend_settings_navigation($settingsnav, $electivenode);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($updated_course)) {

    $answerids = array_keys($updated_course);
    list($insql, $params) = $DB->get_in_or_equal($answerids, SQL_PARAMS_NAMED);
    $answersRecords = $DB->get_records_select('elective_answer', "id $insql", $params, '', 'id, userid');

    $studentSelections = [];

    foreach ($updated_course as $answerid => $courseid) {
        if (!isset($answersRecords[$answerid])) {
            continue;
        }
        $userid = $answersRecords[$answerid]->userid;

        if (!isset($studentSelections[$userid])) {
            $studentSelections[$userid] = [];
        }

        if (in_array($courseid, $studentSelections[$userid])) {
            $error = get_string('duplicateselection', 'mod_elective');
            break;
        }
        $studentSelections[$userid][] = $courseid;
    }

    if (empty($error)) {
        foreach ($updated_course as $answerid => $courseid) {
            $courseRec = $DB->get_record('course', ['id' => $courseid]);
            if ($courseRec) {
                $DB->set_field('elective_answer', 'courseid', $courseid, ['id' => $answerid]);
                $DB->set_field('elective_answer', 'coursefullname', $courseRec->fullname, ['id' => $answerid]);
            }
        }
        redirect(new moodle_url('/mod/elective/answers.php', ['id' => $id]));
    }
}

$sql = "
    SELECT oa.id, oa.userid, q.electivenumber, u.idnumber, oa.courseid, oa.coursefullname, oa.questionid, 
           u.firstname, u.lastname, c.shortname, c.fullname, q.questiontext
    FROM {elective_answer} oa
    JOIN {user} u ON oa.userid = u.id
    JOIN {course} c ON oa.courseid = c.id
    JOIN {elective_question} q ON oa.questionid = q.id
    WHERE oa.instance_id = :instance_id
    ORDER BY u.firstname ASC
";
$params = ['instance_id' => $cm->instance];
$answers = $DB->get_records_sql($sql, $params);

$dateNow = date('Y-m-d');

$moduleInstance = $DB->get_record('elective', array('id' => $cm->instance));
$instanceName = $moduleInstance->name;

if ($action === 'export') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', get_string('studentmark', 'mod_elective'));
    $sheet->setCellValue('B1', get_string('studentfullname', 'mod_elective'));
    $sheet->setCellValue('C1', get_string('selectedcourse', 'mod_elective'));
    $sheet->setCellValue('D1', get_string('courseshortname', 'mod_elective'));
    $sheet->setCellValue('E1', get_string('electivenumber', 'mod_elective'));
    $sheet->setCellValue('F1', get_string('availableoptions', 'mod_elective'));
    $sheet->setCellValue('G1', get_string('questiontext', 'mod_elective'));

    foreach (range('A', 'G') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $sheet->getStyle('A1:G1')->getFont()->setBold(true);
    $row = 2;
    foreach ($answers as $answer) {
        $course_options = get_course_options_for_question($answer->questionid, $DB);

        $sheet->setCellValue('A' . $row, $answer->idnumber);
        $sheet->setCellValue('B' . $row, $answer->firstname . ' ' . $answer->lastname);
        $sheet->setCellValue('C' . $row, $answer->fullname);
        $sheet->setCellValue('D' . $row, $answer->shortname);
        $sheet->setCellValue('E' . $row, $answer->electivenumber);
        $sheet->setCellValue('F' . $row, implode(', ', array_map(fn($course) => $course->fullname, $course_options)));
        $sheet->setCellValue('G' . $row, $answer->questiontext);
        $row++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='. 'Electives_' . $instanceName . '_' . $dateNow . '.xlsx');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

echo $OUTPUT->header();

if (!empty($error)) {
    echo $OUTPUT->notification($error, 'notifyproblem');
}

echo '<form method="post">';
echo '<table id="answers-table" style="width:100%; border-collapse: collapse;">';
echo '<thead><tr>';
echo '<th style="border: 1px solid #ddd; padding: 8px;">' . get_string('studentmark', 'mod_elective') . '</th>';
echo '<th style="border: 1px solid #ddd; padding: 8px;">' . get_string('studentfullname', 'mod_elective') . '</th>';
echo '<th style="border: 1px solid #ddd; padding: 8px;">' . get_string('courseshortname', 'mod_elective') . '</th>';
echo '<th style="border: 1px solid #ddd; padding: 8px;">' . get_string('electivenumber', 'mod_elective') . '</th>';
echo '<th style="border: 1px solid #ddd; padding: 8px;">' . get_string('questiontext', 'mod_elective') . '</th>';
echo '<th style="border: 1px solid #ddd; padding: 8px;">' . get_string('availableoptions', 'mod_elective') . '</th>';
echo '<th style="border: 1px solid #ddd; padding: 8px;">' . get_string('selectedcourse', 'mod_elective') . '</th>';
echo '</tr></thead><tbody>';


$deleteStudentElectivesNotification = get_string('deletestudentelectivesnotification', 'mod_elective');
echo "<script>let deleteStudentElectivesNotification  = '$deleteStudentElectivesNotification';</script>";

$student_colors = [];
$colors = ['#f0f8ff', '#d9f7be', '#e6f7ff', '#fffbe6', '#ffd6e7'];
$color_index = 0;

foreach ($answers as $answer) {
    if (!isset($student_colors[$answer->idnumber])) {
        $student_colors[$answer->idnumber] = $colors[$color_index % count($colors)];
        $color_index++;
    }
    $row_color = $student_colors[$answer->idnumber];

    $options = get_course_options_for_question($answer->questionid, $DB);

    echo '<tr style="background-color:' . $row_color . ';">';
    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . $answer->idnumber . '</td>';
    echo '<td style="border: 1px solid #ddd; padding: 8px;">'
        . $answer->firstname . ' ' . $answer->lastname
        . ' <a href="#" onclick="deleteStudentElectives(' . $answer->userid . ', ' . $cm->instance . ')">'
        . $OUTPUT->pix_icon('t/delete', get_string('deleteElectives'), 'moodle', array('class' => 'iconsmall'))
        . '</a></td>';
    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . $answer->shortname . '</td>';
    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . $answer->electivenumber . '</td>';
    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . $answer->questiontext . '</td>';
    echo '<td style="border: 1px solid #ddd; padding: 8px;">' . implode(', ', array_map(fn($c) => $c->fullname, $options)) . '</td>';
    echo '<td class="scc" style="border: 1px solid #ddd; padding: 8px;">';
    echo '<select class="sc" name="selected_course[' . $answer->id . ']">';
    foreach ($options as $courseOption) {
        $selected = ($courseOption->id == $answer->courseid) ? 'selected' : '';
        echo '<option value="' . $courseOption->id . '" ' . $selected . '>' . $courseOption->fullname . '</option>';
    }
    echo '</select></td></tr>';
}

echo '</tbody></table>';
echo '<br>';
echo '<button class="sc" type="submit" style="margin-top: 10px; padding: 10px; font-size: 16px;">' . get_string('savechanges', 'mod_elective') . '</button>';
echo '</form>';

echo '<form method="get" action="">';
echo '<input type="hidden" name="id" value="' . $id . '">';
echo '<input type="hidden" name="action" value="export">';
echo '<button class="sc" type="submit" style="margin-top: 10px; padding: 10px; font-size: 16px;">' . get_string('exporttoexcel', 'mod_elective') . '</button>';
echo '</form>';

echo $OUTPUT->footer();
