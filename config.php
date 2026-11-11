<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Project N theme configuration.
 *
 * Boost layouts and templates remain the default. Project N adds its own
 * renderer for the personal dashboard and a global navigation through the
 * output hook. Native contextual navigation and page layouts remain owned
 * by Moodle.
 */

$THEME->name = 'projectn';
$THEME->parents = ['boost'];
$THEME->sheets = [];
$THEME->editor_sheets = [];
// Keep Boost's preset as the base SCSS, then append Project N component styles.
// Declaring the callback explicitly is required for child themes that provide
// their own SCSS callback; otherwise the Project N extra SCSS can be omitted.
$THEME->scss = function($theme) {
    return theme_boost_get_main_scss_content($theme);
};
$THEME->prescsscallback = 'theme_boost_get_pre_scss';
$THEME->extrascsscallback = 'theme_projectn_get_extra_scss';
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
