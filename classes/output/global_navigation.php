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
 * Data provider for the Project N global navigation.
 *
 * @package theme_projectn
 */
class global_navigation {
    /**
     * Build the Mustache context for the global navigation.
     *
     * This class only describes destinations. It does not grant permissions:
     * the destination pages remain responsible for their access checks.
     *
     * @return array
     */
    public static function get_template_context(): array {
        global $PAGE, $USER;

        $currentpath = $PAGE->url instanceof \moodle_url ? $PAGE->url->get_path() : '';
        $homeurl = new \moodle_url('/');
        $coursesurl = new \moodle_url('/my/courses.php');
        $calendarurl = new \moodle_url('/calendar/view.php');
        $profileurl = new \moodle_url('/user/profile.php', ['id' => $USER->id]);

        // moodle_url stores the site-root path as an empty string and may retain
        // a trailing slash for directory URLs. Normalise before comparing paths.
        $normalisepath = static function(string $path): string {
            $path = rtrim($path, '/');
            return $path === '' ? '/' : $path;
        };
        $currentpath = $normalisepath($currentpath);
        $homepath = $normalisepath($homeurl->get_path());
        $dashboardpaths = [
            $normalisepath((new \moodle_url('/my/'))->get_path()),
            $normalisepath((new \moodle_url('/my/index.php'))->get_path()),
        ];

        // Treat the personal dashboard as the user's home workspace as well.
        $ishome = $currentpath === $homepath || in_array($currentpath, $dashboardpaths, true);

        $items = [
            [
                'key' => 'home',
                'label' => get_string('home', 'theme_projectn'),
                'url' => $homeurl->out(false),
                'icon' => 'i/home',
                'current' => $ishome,
            ],
            [
                'key' => 'courses',
                'label' => get_string('courses', 'theme_projectn'),
                'url' => $coursesurl->out(false),
                'icon' => 'i/course',
                'current' => $currentpath === $normalisepath($coursesurl->get_path()),
            ],
            [
                'key' => 'calendar',
                'label' => get_string('calendar', 'theme_projectn'),
                'url' => $calendarurl->out(false),
                'icon' => 'i/calendar',
                'current' => $currentpath === $normalisepath($calendarurl->get_path()),
            ],
            [
                'key' => 'profile',
                'label' => get_string('profile'),
                'url' => $profileurl->out(false),
                'icon' => 'i/user',
                'current' => $currentpath === $normalisepath($profileurl->get_path()),
            ],
        ];

        return [
            'label' => get_string('globalnavigation', 'theme_projectn'),
            'items' => $items,
        ];
    }
}
