<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/elective/backup/moodle2/backup_elective_stepslib.php');

class backup_elective_activity_task extends backup_activity_task
{
    /**
     * Defines settings for the backup process.
     */
    protected function define_my_settings()
    {
        // No specific settings for this activity.
    }

    /**
     * Defines steps for the backup process.
     */
    protected function define_my_steps()
    {
        // Add the step to backup elective data.
        $this->add_step(new backup_elective_activity_structure_step('elective_structure', 'elective.xml'));
    }

    /**
     * Encodes content links for the activity.
     *
     * @param string $content Content to encode.
     * @return string Encoded content.
     */
    public static function encode_content_links($content)
    {
        return $content;
    }
}
