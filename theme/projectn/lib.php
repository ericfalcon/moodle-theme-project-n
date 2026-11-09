<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Return the additional SCSS for Project N.
 *
 * Boost remains responsible for its base SCSS. This callback adds only
 * Project N tokens and component styles, avoiding a copy of Boost's source.
 *
 * @param theme_config $theme The theme configuration object.
 * @return string
 */
function theme_projectn_get_extra_scss($theme): string {
    $scssfile = __DIR__ . '/scss/projectn.scss';
    if (!is_readable($scssfile)) {
        debugging('Project N SCSS file is not readable.', DEBUG_DEVELOPER);
        return '';
    }

    return (string)file_get_contents($scssfile);
}
