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
 * Tests for the learner/teacher course dashboard data provider.
 *
 * @package theme_projectn
 * @coversDefaultClass \theme_projectn\output\learner_dashboard
 */
final class learner_dashboard_test extends \advanced_testcase {
    /**
     * A user without active course enrolments receives both empty states.
     *
     * @covers ::get_template_context
     */
    public function test_empty_state_for_user_without_enrolments(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $context = learner_dashboard::get_template_context();

        $this->assertFalse($context['haslearnercourses']);
        $this->assertSame([], $context['learnercourses']);
        $this->assertFalse($context['hasteachercourses']);
        $this->assertSame([], $context['teachercourses']);
        $this->assertFalse($context['hasanycourses']);
        $this->assertNotEmpty($context['emptytitle']);
        $this->assertNotEmpty($context['teacheremptytitle']);
    }

    /**
     * A student enrolment appears in the learner section with a native URL.
     *
     * @covers ::get_template_context
     */
    public function test_student_course_is_classified_as_learner(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation apprenant',
            'shortname' => 'LEARN-01',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $instance = $DB->get_record('enrol', [
            'courseid' => $course->id,
            'enrol' => 'manual',
        ], '*', MUST_EXIST);
        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);
        enrol_get_plugin('manual')->enrol_user($instance, $user->id, $studentrole->id);

        $context = learner_dashboard::get_template_context();

        $this->assertTrue($context['haslearnercourses']);
        $this->assertCount(1, $context['learnercourses']);
        $this->assertSame('Formation apprenant', $context['learnercourses'][0]['fullname']);
        $this->assertStringContainsString(
            '/course/view.php?id=' . $course->id,
            $context['learnercourses'][0]['url']
        );
        $this->assertFalse($context['hasteachercourses']);
    }

    /**
     * A non-editing teacher archetype is classified in the teacher section.
     *
     * @covers ::get_template_context
     */
    public function test_teacher_course_is_classified_as_teacher(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation enseignée',
            'shortname' => 'TEACH-01',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $instance = $DB->get_record('enrol', [
            'courseid' => $course->id,
            'enrol' => 'manual',
        ], '*', MUST_EXIST);
        $teacherroles = get_archetype_roles('teacher');
        $teacherrole = reset($teacherroles);
        enrol_get_plugin('manual')->enrol_user($instance, $user->id, $teacherrole->id);

        $context = learner_dashboard::get_template_context();

        $this->assertFalse($context['haslearnercourses']);
        $this->assertTrue($context['hasteachercourses']);
        $this->assertCount(1, $context['teachercourses']);
        $this->assertSame('Formation enseignée', $context['teachercourses'][0]['fullname']);
        $this->assertStringContainsString(
            '/course/view.php?id=' . $course->id,
            $context['teachercourses'][0]['url']
        );
    }

    /**
     * One account can be a learner in one course and a teacher in another.
     *
     * @covers ::get_template_context
     */
    public function test_user_can_appear_in_both_sections(): void {
        global $DB;

        $this->resetAfterTest();
        $learnercourse = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation suivie',
            'shortname' => 'LEARN-02',
        ]);
        $teachercourse = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation animée',
            'shortname' => 'TEACH-02',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $manual = enrol_get_plugin('manual');

        $learnerinstance = $DB->get_record('enrol', [
            'courseid' => $learnercourse->id,
            'enrol' => 'manual',
        ], '*', MUST_EXIST);
        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);
        $manual->enrol_user($learnerinstance, $user->id, $studentrole->id);

        $teacherinstance = $DB->get_record('enrol', [
            'courseid' => $teachercourse->id,
            'enrol' => 'manual',
        ], '*', MUST_EXIST);
        $teacherroles = get_archetype_roles('teacher');
        $teacherrole = reset($teacherroles);
        $manual->enrol_user($teacherinstance, $user->id, $teacherrole->id);

        $context = learner_dashboard::get_template_context();

        $this->assertCount(1, $context['learnercourses']);
        $this->assertSame('Formation suivie', $context['learnercourses'][0]['fullname']);
        $this->assertCount(1, $context['teachercourses']);
        $this->assertSame('Formation animée', $context['teachercourses'][0]['fullname']);
    }
}
