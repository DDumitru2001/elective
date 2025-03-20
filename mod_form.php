<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
/**
 * The main mod_elective configuration form.
 *
 * @package     mod_elective
 * @copyright   2024 Danica Dumitru
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_elective
 * @copyright   2024 Danica Dumitru
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_elective_mod_form extends moodleform_mod {

    /**
     * Defines form elements for the module.
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // General section for module name and introduction.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name of the elective module.
        $mform->addElement('text', 'name', get_string('electivename', 'mod_elective'), array('size' => '64'));
        $mform->setType('name', !empty($CFG->formatstringstriptags) ? PARAM_TEXT : PARAM_CLEANHTML);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'electivename', 'mod_elective');

        // Module introduction.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Custom settings section.
//        $mform->addElement('header', 'customsettings', get_string('electivefieldset', 'mod_elective'));
//
//        // Question type selection.
//        $mform->addElement('select', 'questiontype', get_string('questiontype', 'mod_elective'), array(
//            'multiple' => get_string('multiplechoice', 'mod_elective'),
//            'single' => get_string('singlechoice', 'mod_elective')
//        ));
//        $mform->setDefault('questiontype', 'multiple');

        // Course selection dropdown.
//        $courses = $DB->get_records_menu('course', null, '', 'id, fullname');
//        $mform->addElement('select', 'courseid', get_string('course', 'mod_elective'), $courses);

        // Question text input.
//        $mform->addElement('textarea', 'questiontext', get_string('questiontext', 'mod_elective'), 'wrap="virtual" rows="10" cols="50"');
//        $mform->setType('questiontext', PARAM_TEXT);

        // Standard course module elements.
        $this->standard_coursemodule_elements();

        // Add action buttons (Save and Cancel).
        $this->add_action_buttons();
    }

    /**
     * Data preprocessing for form defaults.
     *
     * @param array $default_values Form default values.
     */
    public function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

        // Example preprocessing (if required for complex fields).
        if (isset($default_values['questiontype'])) {
            $default_values['questiontype'] = strtolower($default_values['questiontype']);
        }
    }
}
