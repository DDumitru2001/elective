<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/course/classes/category.php');

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);

if ($course) {
    $context = context_course::instance($courseid);

    $imageurl = '';
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0, 'itemid, filepath, filename', false); // False to exclude directories

    // Check if there are any overview files and get the first image
    if ($files) {
        foreach ($files as $file) {
            if ($file->is_valid_image()) {
                $imageurl = file_encode_url(
                    "$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/course/overviewfiles/' . $file->get_filepath() . $file->get_filename(),
                    true
                );
                break;
            }
        }
    }

    // Use theme-generated course image if no custom image is set
    if (empty($imageurl)) {
        $imageurl = \core_course\external\course_summary_exporter::get_course_image($course);

        // If no image is returned, use a fallback image
        if (!$imageurl) {
            $imageurl = $OUTPUT->get_generated_image_for_id($courseid);
        }
    }

    // Information about the course
    $courseInfo = [
        'fullname' => $course->fullname,
        'summary' => !empty($course->summary)
            ? format_text($course->summary, $course->summaryformat)
            : get_string('nosummaryavailable', 'mod_elective'),
        'imageurl' => $imageurl,
        'tutors' => []
    ];

    // Get enrolled users with tutor capability
    $users = get_enrolled_users($context, 'moodle/course:viewparticipants');
    if ($users) {
        foreach ($users as $user) {
            $courseInfo['tutors'][] = fullname($user);
        }
    } else {
        $courseInfo['tutors'][] = get_string('notutorsavailable', 'mod_elective');
    }

    // Return course information as JSON
    echo json_encode($courseInfo);
} else {
    // Return an error if the course is not found
    echo json_encode(['error' => 'Course not found']);
}
