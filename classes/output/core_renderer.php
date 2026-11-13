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
 * Project N core renderer.
 *
 * @package theme_projectn
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /**
     * Repair known ARIA defects in native Moodle/Iomad components.
     *
     * This only adjusts page semantics. Native controls and their JavaScript
     * behavior remain owned by Moodle and Bootstrap.
     *
     * @return string
     */
    public function full_header() {
        $header = parent::full_header();
        $menus = [
            'site' => get_string('menusite', 'theme_projectn'),
            'page' => get_string('menupage', 'theme_projectn'),
        ];
        $this->page->requires->js_call_amd('theme_projectn/aria_repair', 'init', [[], $menus]);

        return $header;
    }

    /**
     * Render the native page content, adding the course dashboard on /my/.
     *
     * The dashboard is prepended inside the main content placeholder, after
     * Boost has rendered the page header and navigation.
     *
     * @return string
     */
    public function main_content() {
        $content = parent::main_content();

        if (!isloggedin() || isguestuser()) {
            return $content;
        }

        if (!($this->page->url instanceof \moodle_url)) {
            return $content;
        }

        if (!in_array($this->page->url->get_path(), ['/my/', '/my/index.php'], true)) {
            return $content;
        }

        $context = learner_dashboard::get_template_context();
        $dashboard = $this->render_from_template('theme_projectn/learner_dashboard', $context);

        return $dashboard . $content;
    }
}
