<?php
defined('MOODLE_INTERNAL') || die();

class backup_elective_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        $elective = new backup_nested_element('elective', array('id'), array(
            'course', 'name', 'intro', 'introformat',
            'timecreated', 'timemodified'
        ));

        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', array('id'), array(
            'userid', 'courseid', 'coursefullname', 'questionid', 'instance_id'
        ));

        $questions = new backup_nested_element('questions');
        $question = new backup_nested_element('question', array('id'), array('quizid',
            'questiontext', 'questiontype', 'courseids', 'maxanswers', 'electivenumber'
        ));

        $elective->add_child($questions);
        $questions->add_child($question);
        $question->add_child($answers);
        $answers->add_child($answer);

        $elective->set_source_table('elective', array('id' => backup::VAR_ACTIVITYID));

        $answer->set_source_table(
            'elective_answer',
            array('instance_id' => backup::VAR_ACTIVITYID)
        );

        $question->set_source_table(
            'elective_question',
            array('quizid' => backup::VAR_MODID)
        );

        return $this->prepare_activity_structure($elective);
    }
}
