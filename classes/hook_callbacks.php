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
     * Add global accessibility repairs and the global navigation.
     *
     * Moodle remains responsible for contextual navigation, access checks,
     * course navigation, breadcrumbs and administration menus.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE;

        // A theme hook can be dispatched while another theme is active.
        if ($PAGE->theme->name !== 'projectn') {
            return;
        }

        // Apply the shared ARIA repairs on every Project N page, including
        // guest-facing pages. The module only changes known, narrowly scoped
        // Moodle/IOMAD markup and uses translated names for the two moremenus.
        if (!during_initial_install()) {
            $PAGE->requires->js_call_amd('theme_projectn/aria_repair', 'init', [
                [],
                [
                    'site' => get_string('menusite', 'theme_projectn'),
                    'page' => get_string('menupage', 'theme_projectn'),
                ],
            ]);
        }

        // The global navigation is intended for authenticated, non-guest users.
        if (!isloggedin() || isguestuser()) {
            return;
        }

        // Avoid adding a second navigation layer to specialised layouts.
        $excludedlayouts = ['login', 'popup', 'embedded', 'maintenance', 'print', 'secure'];
        if (in_array($PAGE->pagelayout, $excludedlayouts, true)) {
            return;
        }

        $context = \theme_projectn\output\global_navigation::get_template_context();
        $html = $hook->renderer->render_from_template('theme_projectn/global_navigation', $context);

        // The learner/teacher dashboard must be inserted in the main content,
        // not here: this hook runs before Boost renders its page layout.
        $hook->add_html($html);
    }
}
