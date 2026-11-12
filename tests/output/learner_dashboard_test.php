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
 */
#[\PHPUnit\Framework\Attributes\CoversClass(learner_dashboard::class)]
final class learner_dashboard_test extends \advanced_testcase {
    /**
     * A user without active course enrolments receives the common empty state.
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
        $this->assertFalse($context['hasupcomingactions']);
        $this->assertSame([], $context['upcomingactions']);
        $this->assertNotEmpty($context['emptytitle']);
    }

    /**
     * A student enrolment appears in the learner section with a native URL.
     */
    public function test_student_course_is_classified_as_learner(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation apprenant',
            'shortname' => 'LEARN-01',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        $context = learner_dashboard::get_template_context();

        $this->assertTrue($context['haslearnercourses']);
        $this->assertCount(1, $context['learnercourses']);
        $this->assertSame('Formation apprenant', $context['learnercourses'][0]['fullname']);
        $this->assertStringContainsString(
            '/course/view.php?id=' . $course->id,
            $context['learnercourses'][0]['url']
        );
        $this->assertFalse($context['hasteachercourses']);
        $this->assertFalse($context['learnercourses'][0]['hasprogress']);
        $this->assertFalse($context['learnercourses'][0]['hasnextactivity']);
    }

    /**
     * A non-editing teacher archetype is classified in the teacher section.
     */
    public function test_teacher_course_is_classified_as_teacher(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation enseignée',
            'shortname' => 'TEACH-01',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $teacherroles = get_archetype_roles('teacher');
        $teacherrole = reset($teacherroles);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $teacherrole->id);

        $context = learner_dashboard::get_template_context();

        $this->assertFalse($context['haslearnercourses']);
        $this->assertTrue($context['hasteachercourses']);
        $this->assertCount(1, $context['teachercourses']);
        $this->assertSame('Formation enseignée', $context['teachercourses'][0]['fullname']);
        $this->assertFalse($context['teachercourses'][0]['hasprogress']);
        $this->assertFalse($context['teachercourses'][0]['hasnextactivity']);
        $this->assertStringContainsString(
            '/course/view.php?id=' . $course->id,
            $context['teachercourses'][0]['url']
        );
    }

    /**
     * One account can be a learner in one course and a teacher in another.
     */
    public function test_user_can_appear_in_both_sections(): void {
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
        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);
        $this->getDataGenerator()->enrol_user($user->id, $learnercourse->id, $studentrole->id);

        $teacherroles = get_archetype_roles('teacher');
        $teacherrole = reset($teacherroles);
        $this->getDataGenerator()->enrol_user($user->id, $teachercourse->id, $teacherrole->id);

        $context = learner_dashboard::get_template_context();

        $this->assertCount(1, $context['learnercourses']);
        $this->assertSame('Formation suivie', $context['learnercourses'][0]['fullname']);
        $this->assertCount(1, $context['teachercourses']);
        $this->assertSame('Formation animée', $context['teachercourses'][0]['fullname']);
    }


    /**
     * Iomad course-editor roles are recognised by their stable shortname.
     *
     * This keeps the Moodle-first theme compatible with Iomad without
     * depending on Iomad classes or constants in Moodle core.
     */
    public function test_iomad_company_course_editor_role_is_classified_as_teacher(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation Iomad',
            'shortname' => 'IOMAD-TEACH',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        // Iomad already defines this role; create a compatible fixture only
        // when the test runs on standard Moodle.
        global $DB;
        $iomadrole = $DB->get_record('role', ['shortname' => 'companycourseeditor']);
        if (!$iomadrole) {
            $iomadroleid = create_role(
                'Iomad company course editor',
                'companycourseeditor',
                'Iomad course editor test role',
                ''
            );
        } else {
            $iomadroleid = $iomadrole->id;
        }
        role_assign($iomadroleid, $user->id, \context_course::instance($course->id)->id);

        $context = learner_dashboard::get_template_context();

        $this->assertFalse($context['haslearnercourses']);
        $this->assertTrue($context['hasteachercourses']);
        $this->assertCount(1, $context['teachercourses']);
        $this->assertSame('Formation Iomad', $context['teachercourses'][0]['fullname']);
    }

    /**
     * A manager role is not mistaken for a teaching role.
     */
    public function test_manager_role_is_not_classified_as_teacher(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation de gestion',
            'shortname' => 'MANAGER-01',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        $managerroles = get_archetype_roles('manager');
        $managerrole = reset($managerroles);
        role_assign($managerrole->id, $user->id, \context_course::instance($course->id)->id);

        $context = learner_dashboard::get_template_context();

        $this->assertTrue($context['haslearnercourses']);
        $this->assertFalse($context['hasteachercourses']);
        $this->assertSame('Formation de gestion', $context['learnercourses'][0]['fullname']);
    }

    /**
     * Learner cards expose native completion progress and the next incomplete activity.
     */
    public function test_learner_course_exposes_progress_and_next_activity(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'Formation avec suivi',
            'shortname' => 'PROGRESS-01',
            'enablecompletion' => 1,
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $studentroles = get_archetype_roles('student');
        $studentrole = reset($studentroles);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        $firstpage = $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'name' => 'Lire le support initial',
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => 1,
        ]);
        $secondpage = $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'name' => 'Consulter le second support',
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => 1,
        ]);

        // Mark the first activity complete to verify that the dashboard
        // calculates a partial percentage and points to the next incomplete one.
        $completioninfo = new \completion_info($course);
        $firstcm = get_coursemodule_from_instance('page', $firstpage->id, $course->id);
        $completioninfo->update_state($firstcm, COMPLETION_COMPLETE, $user->id);

        $context = learner_dashboard::get_template_context();

        $this->assertTrue($context['haslearnercourses']);
        $this->assertTrue($context['learnercourses'][0]['hasprogress']);
        $this->assertSame(50, $context['learnercourses'][0]['progresspercent']);
        $this->assertTrue($context['learnercourses'][0]['hasnextactivity']);
        $this->assertSame(
            'Consulter le second support',
            $context['learnercourses'][0]['nextactivityname']
        );
        $this->assertStringContainsString(
            '/mod/page/view.php?id=',
            $context['learnercourses'][0]['nextactivityurl']
        );
        $this->assertStringContainsString(
            (string)$secondpage->id,
            $context['learnercourses'][0]['nextactivityurl']
        );
    }
}
