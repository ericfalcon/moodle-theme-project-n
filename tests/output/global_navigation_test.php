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
 * @coversDefaultClass \theme_projectn\output\global_navigation
 */
final class global_navigation_test extends \advanced_testcase {
    /**
     * The courses destination is marked current on the user's course list.
     *
     * @covers ::get_template_context
     */
    public function test_courses_destination_is_current(): void {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_url(new \moodle_url('/my/courses.php'));

        $context = global_navigation::get_template_context();

        $this->assertCount(2, $context['items']);
        $this->assertFalse($context['items'][0]['current']);
        $this->assertTrue($context['items'][1]['current']);
        $this->assertSame(get_string('courses', 'theme_projectn'), $context['items'][1]['label']);
    }

    /**
     * The home destination is marked current on the site root.
     *
     * @covers ::get_template_context
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
     * A different page does not incorrectly mark a global destination current.
     *
     * @covers ::get_template_context
     */
    public function test_unrelated_page_has_no_current_global_destination(): void {
        global $PAGE;

        $this->resetAfterTest();
        $PAGE->set_url(new \moodle_url('/course/view.php', ['id' => 42]));

        $context = global_navigation::get_template_context();

        $this->assertFalse($context['items'][0]['current']);
        $this->assertFalse($context['items'][1]['current']);
    }
}
