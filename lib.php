<?php
defined('MOODLE_INTERNAL') || die();


function mod_elective_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $electivenode) {
    global $PAGE;
    if ($PAGE->navigation->find('mod_elective_answers', navigation_node::TYPE_SETTING)) {
        return;
    }

    if (has_capability('mod/elective:viewanswers', $PAGE->cm->context)) {
        $url = new moodle_url('/mod/elective/answers.php', array('id' => $PAGE->cm->id));
        $node = navigation_node::create(
            get_string('answers', 'elective'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'mod_elective_answers',
            new pix_icon('i/grades', '')
        );

        $node->text = format_string(get_string('answers', 'elective'), true, array('context' => $PAGE->cm->context));
        if (class_exists('theme_adaptable\output\core_renderer')) {
            $PAGE->navigation->add_node($node);
        } else {
            $electivenode->add_node($node);
        }

        if ($PAGE->has_secondary_navigation()) {
            $PAGE->secondarynav->add_node($node);
        }
    }
}


/**
* Saves a new instance of the mod_elective into the database.
*
* Given an object containing all the necessary data, (defined by the form
* in mod_form.php) this function will create a new instance and return the id
* number of the instance.
*
* @param object $moduleinstance An object from the form.
* @param mod_elective_mod_form $mform The form.
* @return int The id of the newly inserted record.
*/
function elective_add_instance($moduleinstance, $mform = null) {
global $DB;
$moduleinstance->timecreated = time();
$id = $DB->insert_record('elective', $moduleinstance);
return $id;
}

/**
* Updates an instance of the mod_elective in the database.
*
* Given an object containing all the necessary data (defined in mod_form.php),
* this function will update an existing instance with new data.
*
* @param object $moduleinstance An object from the form in mod_form.php.
* @param mod_elective_mod_form $mform The form.
* @return bool True if successful, false otherwise.
*/
function elective_update_instance($moduleinstance, $mform = null) {
global $DB;
debugging('Function elective_update_instance called', DEBUG_DEVELOPER);
$moduleinstance->timemodified = time();
$moduleinstance->id = $moduleinstance->instance;
return $DB->update_record('elective', $moduleinstance);
}

/**
* Removes an instance of the mod_elective from the database.
*
* @param int $id Id of the module instance.
* @return bool True if successful, false on failure.
*/
function elective_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('elective', ['id' => $id]);
    error_log("instance: $id");

    if (!$exists) {
        error_log("Instance not found: $id");
        return false;
    }
    $cm = $DB->get_record('course_modules', ['instance' => $id, 'module' => $DB->get_field('modules', 'id', ['name' => 'elective'])]);
    $DB->delete_records('elective_question', ['quizid' => $cm->id]);

    $answers = $DB->get_records('elective_answer', ['instance_id' => $id]);
    if ($answers) {
        $questionids = array_column($answers, 'questionid');
        $DB->delete_records('elective_answer', ['instance_id' => $id]);
        if ($questionids) {
            list($in_sql, $params) = $DB->get_in_or_equal($questionids, SQL_PARAMS_NAMED);
            $DB->delete_records_select('elective_question', "id $in_sql", $params);
            error_log("Deleted questions associated with answers: " . implode(', ', $questionids));
        }
    }

    $DB->delete_records('elective', ['id' => $id]);
    error_log("Deleted elective instance: $id");
    return true;
}

function elective_course_loaded() {
    debugging('elective_course_loaded called', DEBUG_DEVELOPER);
}

/**
 * Define features supported by the elective module.
 *
 * @param string $feature The feature constant.
 * @return mixed True if the feature is supported, null otherwise.
 */
function elective_supports($feature) {
    switch ($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * @param $quizid
 * @return int
 */
function get_current_elective_number($quizid) {
    global $DB;
    return $DB->count_records('elective_question', ['quizid' => $quizid]);
}
