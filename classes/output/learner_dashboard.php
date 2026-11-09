<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace theme_projectn\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Data provider for the learner dashboard.
 *
 * Course enrolments are retrieved through Moodle's enrolment API. This class
 * does not infer roles or implement access checks of its own.
 *
 * @package theme_projectn
 */
class learner_dashboard {
    /** Maximum number of course cards displayed on the dashboard. */
    private const COURSE_LIMIT = 6;

    /**
     * Build the Mustache context for the learner dashboard.
     *
     * @return array
     */
    public static function get_template_context(): array {
        $enrolledcourses = enrol_get_my_courses(
            'id, fullname, shortname, visible',
            'visible DESC, sortorder ASC',
            0,
            true
        );

        $courses = [];
        foreach (array_slice($enrolledcourses, 0, self::COURSE_LIMIT) as $course) {
            $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
            $courses[] = [
                'fullname' => format_string($course->fullname),
                'shortname' => format_string($course->shortname),
                'url' => $courseurl->out(false),
            ];
        }

        return [
            'heading' => get_string('learnerdashboardheading', 'theme_projectn'),
            'description' => get_string('learnerdashboarddescription', 'theme_projectn'),
            'hascourses' => !empty($courses),
            'courses' => $courses,
            'moreurl' => (new \moodle_url('/my/courses.php'))->out(false),
            'morelabel' => get_string('learnerdashboardmore', 'theme_projectn'),
            'emptytitle' => get_string('learnerdashboardemptytitle', 'theme_projectn'),
            'emptydescription' => get_string('learnerdashboardemptydescription', 'theme_projectn'),
        ];
    }
}
