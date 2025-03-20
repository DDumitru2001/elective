<?php

defined('MOODLE_INTERNAL') || die();

class restore_elective_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Defines structure for restoring elective activity data.
     *
     * @return restore_path_element[] Array of restore paths.
     */
    protected function define_structure() {
        $paths = array();
        $paths[] = new restore_path_element('elective', '/activity/elective');
        $paths[] = new restore_path_element('question', '/activity/elective/questions/question');
//        $paths[] = new restore_path_element('answer', '/activity/elective/questions/question/answers/answer');
        // uncomment this line if u want to restore the entire activity
        //
        //currently plugin do not restore the answers given, if u uncomment this line you will be able to restore the entire activity

        return $this->prepare_activity_structure($paths);
    }



    /**
     * Processes the elective activity data.
     *
     * @param array $data Parsed data.
     */
    protected function process_elective($data)
    {
        global $DB;

        $data = (object)$data;
        $newitemid = $DB->insert_record('elective', $data);
        $this->set_mapping('elective', $data->id, $newitemid, true);
        $cmid = $this->task->get_moduleid();
        $DB->set_field('course_modules', 'instance', $newitemid, array('id' => $cmid));
        $this->set_mapping('course_module', $this->task->get_old_moduleid(), $cmid);
    }

    /**
     * Processes the answer data.
     *
     * @param array $data Parsed data.
     */
    protected function process_answer($data)
    {
        global $DB;
        $data = (object)$data;
        $data->instance_id = $this->get_new_parentid('elective');
        $data->questionid = $this->get_new_parentid('question');
        $newitemid = $DB->insert_record('elective_answer', $data);
        $this->set_mapping('answer', $data->id, $newitemid);
    }

    /**
     * Processes the question data.
     *
     * @param array $data Parsed data.
     */
    protected function process_question($data)
    {
        global $DB;
        $data = (object)$data;
        $cmid = $this->task->get_moduleid();
        $data->quizid = $cmid;
        if (!isset($data->electivenumber)) {
            $data->electiveNumber = 0;
        }

        $newitemid = $DB->insert_record('elective_question', $data);
        $this->set_mapping('question', $data->id, $newitemid);
    }

    /**
     * Post-processing after data insertion.
     */
    protected function after_execute()
    {
        $this->add_related_files('mod_elective', 'intro', null);
        $this->add_related_files('course_module', 'mod_elective', null);
    }
}
