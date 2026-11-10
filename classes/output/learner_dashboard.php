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
 * Data provider for the learner dashboard.
 *
 * Courses are classified from role assignments in each course context. Moodle
 * remains responsible for enrolment, capability evaluation and access control.
 *
 * @package theme_projectn
 */
class learner_dashboard {
    /** Maximum number of course cards displayed in each section. */
    private const COURSE_LIMIT = 6;

    /** Iomad role shortnames identifying company-level teaching assignments. */
    private const IOMAD_TEACHER_ROLES = ['companycourseeditor', 'companycoursenoeditor'];

    /**
     * Build the Mustache context for the learner dashboard.
     *
     * @return array
     */
    public static function get_template_context(): array {
        global $DB, $USER;

        $enrolledcourses = enrol_get_my_courses(
            'id, fullname, shortname, visible, enablecompletion',
            'visible DESC, sortorder ASC',
            0
        );

        // Load role archetypes once. Custom roles without an archetype are
        // additionally detected through their effective course capabilities.
        $roles = $DB->get_records('role', [], '', 'id, shortname, archetype');
        $learnercourses = [];
        $teachercourses = [];

        foreach ($enrolledcourses as $course) {
            $coursecontext = \context_course::instance($course->id);
            $assignedroles = get_user_roles($coursecontext, $USER->id, true);
            $isteacher = false;

            foreach ($assignedroles as $assignedrole) {
                $roleid = $assignedrole->roleid;
                $archetype = $roles[$roleid]->archetype ?? '';

                if (in_array($roles[$roleid]->shortname ?? '', self::IOMAD_TEACHER_ROLES, true)
                        || in_array($archetype, ['editingteacher', 'teacher'], true)) {
                    $isteacher = true;
                    break;
                }
            }

            // Custom teaching roles may not have a standard Moodle archetype.
            // Do not let broad manager/admin roles trigger this fallback.
            if (!$isteacher && empty($assignedroles)) {
                $isteacher = has_capability('moodle/course:update', $coursecontext, $USER)
                    || has_capability('moodle/grade:viewall', $coursecontext, $USER);
            } else if (!$isteacher) {
                $hascustomrole = false;
                foreach ($assignedroles as $assignedrole) {
                    $roleid = $assignedrole->roleid;
                    if (empty($roles[$roleid]->archetype)) {
                        $hascustomrole = true;
                        break;
                    }
                }
                if ($hascustomrole) {
                    $isteacher = has_capability('moodle/course:update', $coursecontext, $USER)
                        || has_capability('moodle/grade:viewall', $coursecontext, $USER);
                }
            }

            $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
            $card = [
                // Retain the course object only until visible cards are selected.
                'course' => $course,
                'fullname' => format_string($course->fullname),
                'shortname' => format_string($course->shortname),
                'url' => $courseurl->out(false),
                'hasprogress' => false,
                'hasnextactivity' => false,
            ];

            if ($isteacher) {
                $teachercourses[] = $card;
            } else {
                // The enrolment API has already restricted this list to the
                // current user's active course enrolments.
                $learnercourses[] = $card;
            }
        }

        $learnercourses = array_slice($learnercourses, 0, self::COURSE_LIMIT);
        $teachercourses = array_slice($teachercourses, 0, self::COURSE_LIMIT);

        // Completion, next activities and calendar actions are limited to the
        // learner cards that will be displayed.
        $upcomingactions = [];
        $now = time();
        $actionwindow = $now + (30 * DAYSECS);

        foreach ($learnercourses as &$card) {
            $course = $card['course'];

            // Retrieve actionable calendar events through Moodle's public API.
            // This honours activity callbacks, user/group overrides and visibility.
            $actionevents = \core_calendar\local\api::get_action_events_by_course(
                $course,
                $now,
                $actionwindow,
                null,
                5
            );

            foreach ($actionevents as $actionevent) {
                $action = $actionevent->get_action();
                $eventtime = $actionevent->get_times()->get_sort_time()->getTimestamp();
                $actionurl = $action->get_url();

                $upcomingactions[] = [
                    'name' => format_string($actionevent->get_name()),
                    'coursename' => format_string($course->fullname),
                    'date' => userdate($eventtime, get_string('strftimedatetime', 'langconfig')),
                    'datetime' => gmdate('c', $eventtime),
                    'timestamp' => $eventtime,
                    'hasurl' => $action->is_actionable(),
                    'url' => $actionurl->out(false),
                    'actionlabel' => format_string($action->get_name()),
                ];
            }

            unset($card['course']);

            if (empty($course->enablecompletion)) {
                continue;
            }

            $completioninfo = new \completion_info($course);
            $progress = $completioninfo->get_progress_percentage($USER->id);

            if ($progress !== null) {
                $card['hasprogress'] = true;
                $card['progresspercent'] = (int) round($progress);
                $card['progresslabel'] = get_string(
                    'learnerdashboardprogress',
                    'theme_projectn',
                    (int) round($progress)
                );
            }

            $modinfo = get_fast_modinfo($course, $USER->id);
            foreach ($modinfo->get_cms() as $cm) {
                if (!$cm->uservisible || empty($cm->completion)
                        || !$completioninfo->is_enabled($cm)) {
                    continue;
                }

                $completiondata = $completioninfo->get_data($cm, false, $USER->id);
                if (in_array($completiondata->completionstate, [
                        COMPLETION_COMPLETE,
                        COMPLETION_COMPLETE_PASS,
                        COMPLETION_COMPLETE_FAIL,
                    ], true)) {
                    continue;
                }

                $activityurl = new \moodle_url('/mod/' . $cm->modname . '/view.php', [
                    'id' => $cm->id,
                ]);
                $card['hasnextactivity'] = true;
                $card['nextactivitylabel'] = get_string(
                    'learnerdashboardnextactivity',
                    'theme_projectn'
                );
                $card['nextactivityname'] = format_string($cm->name);
                $card['nextactivityurl'] = $activityurl->out(false);
                break;
            }
        }
        unset($card);

        foreach ($teachercourses as &$card) {
            unset($card['course']);
        }
        unset($card);

        // Merge events from the displayed learner courses and keep the five
        // nearest actions, independently of the number of enrolled courses.
        usort($upcomingactions, static function($left, $right): int {
            return $left['timestamp'] <=> $right['timestamp'];
        });
        $upcomingactions = array_slice($upcomingactions, 0, 5);
        foreach ($upcomingactions as &$upcomingaction) {
            unset($upcomingaction['timestamp']);
        }
        unset($upcomingaction);

        return [
            'hasupcomingactions' => !empty($upcomingactions),
            'upcomingactions' => $upcomingactions,
            'upcomingactionsheading' => get_string('upcomingactionsheading', 'theme_projectn'),
            'upcomingactionsdescription' => get_string('upcomingactionsdescription', 'theme_projectn'),
            'learnerheading' => get_string('learnerdashboardheading', 'theme_projectn'),
            'learnerdescription' => get_string('learnerdashboarddescription', 'theme_projectn'),
            'haslearnercourses' => !empty($learnercourses),
            'learnercourses' => $learnercourses,
            'teacherheading' => get_string('teacherdashboardheading', 'theme_projectn'),
            'teacherdescription' => get_string('teacherdashboarddescription', 'theme_projectn'),
            'hasteachercourses' => !empty($teachercourses),
            'hasanycourses' => !empty($learnercourses) || !empty($teachercourses),
            'teachercourses' => $teachercourses,
            'moreurl' => (new \moodle_url('/my/courses.php'))->out(false),
            'morelabel' => get_string('learnerdashboardmore', 'theme_projectn'),
            'emptytitle' => get_string('learnerdashboardemptytitle', 'theme_projectn'),
            'emptydescription' => get_string('learnerdashboardemptydescription', 'theme_projectn'),
            'teacheremptytitle' => get_string('teacherdashboardemptytitle', 'theme_projectn'),
            'teacheremptydescription' => get_string('teacherdashboardemptydescription', 'theme_projectn'),
        ];
    }
}
