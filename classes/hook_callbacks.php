<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace theme_projectn;

defined('MOODLE_INTERNAL') || die();

/**
 * Output hook callbacks for Project N.
 *
 * @package theme_projectn
 */
class hook_callbacks {
    /**
     * Add the global navigation before the main page layout.
     *
     * Moodle remains responsible for contextual navigation, access checks,
     * course navigation, breadcrumbs and administration menus.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE, $CFG;

        // The global navigation is intended for authenticated, non-guest users.
        if (!isloggedin() || isguestuser()) {
            return;
        }

        // Avoid adding a second navigation layer to specialised layouts.
        $excludedlayouts = ['login', 'popup', 'embedded', 'maintenance', 'print', 'secure'];
        if (in_array($PAGE->pagelayout, $excludedlayouts, true)) {
            return;
        }

        $currentpath = $PAGE->url instanceof \moodle_url ? $PAGE->url->get_path() : '';
        $homeurl = new \moodle_url('/');
        $coursesurl = new \moodle_url('/my/courses.php');

        $context = [
            'label' => get_string('globalnavigation', 'theme_projectn'),
            'items' => [
                [
                    'label' => get_string('home', 'theme_projectn'),
                    'url' => $homeurl->out(false),
                    'current' => $currentpath === $homeurl->get_path(),
                ],
                [
                    'label' => get_string('courses', 'theme_projectn'),
                    'url' => $coursesurl->out(false),
                    'current' => $currentpath === $coursesurl->get_path(),
                ],
            ],
        ];

        $html = $hook->renderer->render_from_template('theme_projectn/global_navigation', $context);
        $hook->add_html($html);
    }
}
