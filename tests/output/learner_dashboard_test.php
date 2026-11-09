<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace theme_projectn;

use theme_projectn\output\learner_dashboard;

/**
 * Tests for the learner dashboard data provider.
 *
 * @package theme_projectn
 * @coversDefaultClass \theme_projectn\output\learner_dashboard
 */
final class learner_dashboard_test extends \advanced_testcase {
    /**
     * A learner with no active course enrolments receives the empty state.
     *
     * @covers ::get_template_context
     */
    public function test_empty_state_for_user_without_enrolments(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $context = learner_dashboard::get_template_context();

        $this->assertFalse($context['hascourses']);
        $this->assertSame([], $context['courses']);
        $this->assertNotEmpty($context['emptytitle']);
        $this->assertNotEmpty($context['emptydescription']);
    }

    /**
     * Course cards use enrolled course records and native course URLs.
     *
     * @covers ::get_template_context
     */
    public function test_enrolled_course_is_exposed_as_native_course_link(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation de test',
            'shortname' => 'TEST-01',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $instance = $DB->get_record('enrol', [
            'courseid' => $course->id,
            'enrol' => 'manual',
        ], '*', MUST_EXIST);
        $roles = get_archetype_roles('student');
        $manual = enrol_get_plugin('manual');
        $studentrole = reset($roles);
        $manual->enrol_user($instance, $user->id, $studentrole->id);

        $context = learner_dashboard::get_template_context();

        $this->assertTrue($context['hascourses']);
        $this->assertCount(1, $context['courses']);
        $this->assertSame('Formation de test', $context['courses'][0]['fullname']);
        $this->assertSame('TEST-01', $context['courses'][0]['shortname']);
        $this->assertStringContainsString('/course/view.php?id=' . $course->id, $context['courses'][0]['url']);
    }
}
