<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace theme_projectn;

use theme_projectn\output\global_navigation;

/**
 * Tests for the Project N global navigation data provider.
 *
 * @package theme_projectn
 */
#[\PHPUnit\Framework\Attributes\CoversClass(global_navigation::class)]
final class global_navigation_test extends \advanced_testcase {
    /**
     * The courses destination is marked current on the user's course list.
     */
    public function test_courses_destination_is_current(): void {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_url(new \moodle_url('/my/courses.php'));

        $context = global_navigation::get_template_context();

        $this->assertCount(4, $context['items']);
        $this->assertFalse($context['items'][0]['current']);
        $this->assertTrue($context['items'][1]['current']);
        $this->assertSame(get_string('courses', 'theme_projectn'), $context['items'][1]['label']);
    }

    /**
     * The home destination is marked current on the site root.
     */
    public function test_home_destination_is_current(): void {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_url(new \moodle_url('/'));

        $context = global_navigation::get_template_context();

        $this->assertTrue($context['items'][0]['current']);
        $this->assertFalse($context['items'][1]['current']);
        $this->assertSame(get_string('home', 'theme_projectn'), $context['items'][0]['label']);
    }

    /**
     * The personal dashboard is treated as the user's home workspace.
     */
    public function test_home_destination_is_current_on_dashboard(): void {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_url(new \moodle_url('/my/'));

        $context = global_navigation::get_template_context();

        $this->assertTrue($context['items'][0]['current']);
        $this->assertFalse($context['items'][1]['current']);
        $this->assertFalse($context['items'][2]['current']);
        $this->assertFalse($context['items'][3]['current']);
    }

    /**
     * A different page does not incorrectly mark a global destination current.
     */
    public function test_unrelated_page_has_no_current_global_destination(): void {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_url(new \moodle_url('/course/view.php', ['id' => 42]));

        $context = global_navigation::get_template_context();

        $this->assertFalse($context['items'][0]['current']);
        $this->assertFalse($context['items'][1]['current']);
        $this->assertFalse($context['items'][2]['current']);
        $this->assertFalse($context['items'][3]['current']);
    }

    /**
     * Calendar and profile destinations are included for the mobile toolbar.
     */
    public function test_calendar_and_profile_destinations_are_available(): void {
        global $PAGE, $USER;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $PAGE->set_url(new \\moodle_url('/calendar/view.php'));

        $context = global_navigation::get_template_context();

        $this->assertCount(4, $context['items']);
        $this->assertSame((new \\moodle_url('/calendar/view.php'))->out(false), $context['items'][2]['url']);
        $this->assertTrue($context['items'][2]['current']);
        $this->assertSame((new \\moodle_url('/user/profile.php', ['id' => $USER->id]))->out(false), $context['items'][3]['url']);
    }
}
