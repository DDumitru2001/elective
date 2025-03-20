<?php
require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID

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

$PAGE->set_url('/mod/elective/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($module->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_module::instance($cm->id));
$settingsnav = $PAGE->settingsnav;
$electivenode = $settingsnav->add(get_string('elective', 'mod_elective'), null, navigation_node::TYPE_CONTAINER);
$context = context_module::instance($cm->id);

mod_elective_extend_settings_navigation($settingsnav, $electivenode);

echo $OUTPUT->header();
echo '<link rel="stylesheet" type="text/css" href="elective.css">';
echo '<script src="elective.js"></script>';

?>

    <div id="courseInfoModal" class="modall" style="position: absolute; display: none;">
        <div class="modal-content" style="padding: 20px; border-radius: 5px;">
            <span class="close" id="closeModal" style="cursor: pointer;">&times;</span>
            <div style="display: flex;">
                <div id="courseDetails" style="flex-grow: 1;">
                    <h2 id="courseTitle"></h2>
                    <p id="courseSummary"></p>
                    <div id="courseTutors">
                        <h3><?php echo get_string('tutors', 'mod_elective');?></h3>
                        <ul id="tutorsList"></ul>
                    </div>
                </div>
                <div id="courseImage" style="width: 200px; height: 200px; background-size: cover; background-position: center; margin-left: 20px;"></div>
            </div>
        </div>
    </div>

<?php

// inject translations into client side
$removeBtnText = get_string('remove', 'mod_elective');
echo "<script>const removeBtnText = '$removeBtnText';</script>";

$removeConfirmation = get_string('confirmationremove', 'mod_elective');
echo "<script>let removeConfirmation = '$removeConfirmation';</script>";

$alertMultipleOptionsExceeded = get_string('alertMultipleOptionsExceeded', 'mod_elective');
$alertMultipleOptionsExceeded2 = get_string('alertMultipleOptionsExceeded2', 'mod_elective');
echo "<script>let alertMultipleOptionsExceeded = '$alertMultipleOptionsExceeded';</script>";
echo "<script>let alertMultipleOptionsExceeded2 = '$alertMultipleOptionsExceeded2';</script>";

if (has_capability('mod/elective:viewanswers', $context)) {

    echo '<h2>' . get_string('createelective', 'mod_elective') . '</h2>';
    echo '<form id="question-form" action="save_question.php" method="post" style="max-width: 600px; margin: auto; background-color: #f0f8ff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">';

    echo '<div style="margin-bottom: 10px;">';
    echo '<label for="electivenumber" style="display: block; font-weight: bold; color: #000080;">' . get_string('electivenumber', 'mod_elective') . ':</label>';
    $currentElectiveNumber = get_current_elective_number($cm->id) +1;
    echo '<div id="electivenumber" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #000080; border-radius: 4px; background-color: white; color: #000;">';
    echo htmlspecialchars($currentElectiveNumber);
    echo '</div>';
    echo '</div>';
    echo '<input type="hidden" name="electivenumber" value="' . $currentElectiveNumber = get_current_elective_number($cm->id) +1 . '">';

    echo '<div style="margin-bottom: 10px;">';
    echo '<label for="questiontype" style="display: block; font-weight: bold; color: #000080;">' . get_string('questiontype', 'mod_elective') . ':</label>';
    echo '<select name="questiontype" id="questiontype" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #000080; border-radius: 4px; background-color: white;" onchange="toggleMaxAnswersField()">';
    echo '<option value="multiple">' . get_string('multiplechoice', 'mod_elective') . '</option>';
    echo '<option value="single">' . get_string('singlechoice', 'mod_elective') . '</option>';
    echo '</select>';
    echo '</div>';


    echo '<div id="max-answers-group" style="margin-bottom: 10px; display: none;">';
    echo '<label for="maxanswers" style="display: block; font-weight: bold; color: #000080;">' . get_string('maxanswers', 'mod_elective') . '</label>';
    echo '<input type="number" name="maxanswers" id="maxanswers" min="2" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #000080; border-radius: 4px; background-color: white;">';
    echo '</div>';

    echo '<div style="margin-bottom: 10px;">';
    echo '<label for="course-search" style="display: block; font-weight: bold; color: #000080;">' . get_string('searchcourses', 'mod_elective') . '</label>';
    echo '<input type="text" id="course-search" placeholder="Search courses" onfocus="showDropdown()" onkeyup="filterCourses()" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #000080; border-radius: 4px;">';
    echo '<input type="hidden" name="quizid" id="quizid" value="' . $cm->id . '">';

    echo '<select id="course-select" size="5" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #000080; border-radius: 4px; display: none;" onclick="addCourse()">';
    $courses = $DB->get_records('course', ['visible' => 1]);
    foreach ($courses as $course) {
        echo '<option value="' . $course->id . '">' . format_string($course->fullname) . '</option>';
    }
    echo '</select>';
    echo '<button type="button" id="clear-courses-btn" onclick="clearCourses()" style="width: 100%; padding: 10px; margin-top: 10px; background-color: #000080; color: white; border: none; border-radius: 4px;">' . get_string('removeallcourses', 'mod_elective') . '</button>';    echo '</div>';

    echo '<div id="selected-courses-container" style="margin-bottom: 10px;">';
    echo '<p id="added-courses-text" style="display:none; font-weight: bold; color: #000080;">' . get_string('addedcourses', 'mod_elective') . '</p>';
    echo '<div id="selected-courses"></div>';
    echo '</div>';

    echo '<div style="margin-bottom: 10px;">';
    echo '<label for="questiontext" style="display: block; font-weight: bold; color: #000080;">' . get_string('electivetext', 'mod_elective') . '</label>';
    echo '<textarea name="questiontext" id="questiontext" rows="4" cols="30" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #000080; border-radius: 4px;"></textarea>';
    echo '</div>';

    echo '<input type="hidden" name="courseids" id="courseids">';
    echo '<input type="hidden" name="quizid" value="' . $cm->id . '">';
    echo '<input type="hidden" name="id" value="' . $id . '">';
    echo '<input type="submit" value="' . get_string('saveelective', 'mod_elective') . '" style="width: 100%; padding: 10px; font-size: 16px; background-color: #000080; color: white; border: none; border-radius: 4px;">';
    echo '</form>';
    echo '<h2>' . get_string('createdelectives', 'mod_elective') . '</h2>';
    $questions = $DB->get_records_sql("SELECT * FROM {elective_question} WHERE quizid = :quizid", ['quizid' => $cm->id]);
    foreach ($questions as $question) {
        echo '<div id="question-' . $question->id . '" style="border: 1px solid #000080; padding: 10px; margin-bottom: 10px; background-color: #f0f8ff; border-radius: 8px;">';
        echo '<h3 style="color: #000080;"> ' . get_string('electivenumber', 'mod_elective') . ' ' . $question->electivenumber . '</h3>';
        echo '<h3 style="color: #000080;">' . $question->questiontext . '</h3>';
        $courseids = strpos($question->courseids, ',') !== false ? explode(',', $question->courseids) : [$question->courseids];
        foreach ($courseids as $courseid) {
            $coursefullname = $DB->get_field('course', 'fullname', array('id' => $courseid));
            echo '<p style="color: #000080;">' . $coursefullname . ' ';
            echo '<span class="info-icon" data-courseid="' . $courseid . '" style="cursor: pointer;"><i class="fa fa-info-circle" aria-hidden="true"></i></span></p>';
        }
        $deleteElectiveText = get_string('deleteelectivequestion', 'mod_elective'); // Obținem textul tradus
        echo '<button onclick="deleteQuestion(' . $question->id . ')" style="padding: 5px 10px; margin-top: 10px; background-color: #000080; color: white; border: none; border-radius: 4px;">' . htmlspecialchars($deleteElectiveText) . '</button>';
        echo '</div>';
    }
}
if (has_capability('mod/elective:answerformquestions', $context)) {
    $studentAnswers = $DB->get_records('elective_answer', ['userid' => $USER->id, 'instance_id' => $cm->instance]);

    if ($studentAnswers) {
        echo '<h2>' . get_string('yoursubmittedanswers', 'mod_elective') . '</h2>';
        $questions = $DB->get_records_sql("SELECT * FROM {elective_question} WHERE quizid = :quizid", ['quizid' => $cm->id]);

        foreach ($questions as $question) {
            echo '<div class="question-container">';
            echo '<h3 style="color: #000080;"> ' . get_string('electivenumber', 'mod_elective') . ' ' . $question->electivenumber . '</h3>';
            echo '<h3>' . $question->questiontext . '</h3>';
            $courseids = strpos($question->courseids, ',') !== false ? explode(',', $question->courseids) : [$question->courseids];

            if ($question->questiontype == 'single') {
                foreach ($courseids as $courseid) {
                    $coursefullname = $DB->get_field('course', 'fullname', array('id' => $courseid));
                    $isChecked = $DB->record_exists('elective_answer', ['userid' => $USER->id, 'courseid' => $courseid, 'instance_id' => $cm->instance]) ? 'checked' : '';
                    echo '<input type="radio" name="question_' . $question->id . '" value="' . $courseid . '" ' . $isChecked . ' disabled> ' . $coursefullname . ' <span class="info-icon" data-courseid="' . $courseid . '" title="More Info"><i class="fa fa-info-circle" aria-hidden="true"></i></span><br>';
                }
            } elseif ($question->questiontype == 'multiple') {
                foreach ($courseids as $courseid) {
                    $coursefullname = $DB->get_field('course', 'fullname', array('id' => $courseid));
                    $isChecked = $DB->record_exists('elective_answer', ['userid' => $USER->id, 'courseid' => $courseid, 'instance_id' => $cm->instance]) ? 'checked' : '';
                    echo '<input type="checkbox" name="question_' . $question->id . '[]" value="' . $courseid . '" class="multiple-choice" ' . $isChecked . ' disabled> ' . $coursefullname . ' <span class="info-icon" data-courseid="' . $courseid . '" title="More Info"><i class="fa fa-info-circle" aria-hidden="true"></i></span><br>';
                }
            }
            echo '</div>';
        }
    } else {
        if (has_capability('mod/elective:view', $context)) {
            echo '<h2>' . get_string('chooseelective', 'mod_elective') . '</h2>';            $questions = $DB->get_records_sql("SELECT * FROM {elective_question} WHERE quizid = :quizid", ['quizid' => $cm->id]);

            echo '<form id="student-questions-form" action="save_answers.php" method="post" onsubmit="return validateForm()">';

            foreach ($questions as $question) {
                echo '<div class="question-container">';
                echo '<h3 style="color: #000080;"> ' . get_string('electivenumber', 'mod_elective') . ' ' . $question->electivenumber . '</h3>';
                echo '<h3>' . $question->questiontext . '</h3>';
                $courseids = strpos($question->courseids, ',') !== false ? explode(',', $question->courseids) : [$question->courseids];

                if ($question->questiontype == 'single') {
                    echo '<div>';
                    foreach ($courseids as $courseid) {
                        $coursefullname = $DB->get_field('course', 'fullname', array('id' => $courseid));
                        echo '<input type="radio" name="question_' . $question->id . '" value="' . $courseid . '"> ' . $coursefullname . ' <span class="info-icon" data-courseid="' . $courseid . '" title="More Info"><i class="fa fa-info-circle" aria-hidden="true"></i></span><br>';
                    }
                    echo '</div>';
                } elseif ($question->questiontype == 'multiple') {
                    echo '<div>';
                    foreach ($courseids as $courseid) {
                        $coursefullname = $DB->get_field('course', 'fullname', array('id' => $courseid));
                        echo '<input type="checkbox" name="question_' . $question->id . '[]" value="' . $courseid . '" class="multiple-choice"> ' . $coursefullname . ' <span class="info-icon" data-courseid="' . $courseid . '" title="More Info"><i class="fa fa-info-circle" aria-hidden="true"></i></span><br>';
                    }
                    echo '<input type="hidden" id="maxanswers_' . $question->id . '" value="' . $question->maxanswers . '">';
                    echo '</div>';
                }
                echo '</div>';
            }

            echo '<input type="hidden" name="id" value="' . $id . '">';
            echo '<input type="hidden" name="quizid" value="' . $cm->instance . '">';
            $submitAnswersText = get_string('submitanswers', 'mod_elective');
            echo '<input type="submit" value="' . htmlspecialchars($submitAnswersText) . '" style="width: 100%; padding: 10px; font-size: 16px; background-color: #000080; color: white; border: none; border-radius: 4px;">';
            echo '</form>';
        }
    }
}

echo $OUTPUT->footer();
