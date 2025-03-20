<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/elective/backup/moodle2/restore_elective_stepslib.php');

class restore_elective_activity_task extends restore_activity_task
{
    /**
     * Defines settings for the restore process.
     */
    protected function define_my_settings()
    {
        // No specific settings for this activity.
    }

    /**
     * Defines steps for the restore process.
     */
    protected function define_my_steps()
    {
        // Add the step to restore elective data.
        $this->add_step(new restore_elective_activity_structure_step('elective_structure', 'elective.xml'));
    }

    /**
     * Decodes content links for the activity.
     *
     * @param string $content Content to decode.
     * @return string Decoded content.
     */
    public static function decode_content_links($content)
    {
        return $content;
    }

    /**
     * Returns the file areas associated with this activity.
     *
     * @return array File areas.
     */
    public static function define_decode_contents()
    {
        return array();
    }
    public static function define_decode_rules()
    {
        return array();
    }
}
