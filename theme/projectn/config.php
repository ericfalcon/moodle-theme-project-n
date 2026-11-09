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
 * Keep Boost layouts and templates as the default. Project N only adds
 * its own design tokens at this stage; navigation and page layouts will
 * be changed only after their responsive and accessibility contracts
 * have been prototyped and documented.
 */

$THEME->name = 'projectn';
$THEME->parents = ['boost'];
$THEME->sheets = [];
$THEME->editor_sheets = [];
$THEME->extrascsscallback = 'theme_projectn_get_extra_scss';
