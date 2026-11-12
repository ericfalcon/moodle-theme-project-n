<?php
// Prepare a real, non-privileged learner and an enrolled course for browser tests.
define('CLI_SCRIPT', true);
$platformdir = getenv('MOODLE_DIR');
if (!$platformdir || !is_file($platformdir . '/config.php')) {
    fwrite(STDERR, "MOODLE_DIR does not point to an installed platform.\n");
    exit(1);
}
require($platformdir . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/enrollib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/lib/resourcelib.php');

global $DB, $CFG;
\core\session\manager::set_user(get_admin());

$categoryid = $DB->get_field('course_categories', 'id', ['id' => 1], MUST_EXIST);
$shortname = 'projectn-learner-ci';
if ($DB->record_exists('course', ['shortname' => $shortname])) {
    $course = $DB->get_record('course', ['shortname' => $shortname], '*', MUST_EXIST);
} else {
    $course = create_course((object)[
        'category' => $categoryid,
        'fullname' => 'Project N learner validation',
        'shortname' => $shortname,
        'format' => 'topics',
        'numsections' => 2,
        'visible' => 1,
        'enablecompletion' => 1,
    ]);
}

$username = 'projectnlearner';
$password = 'ProjectN-Learner!2026';
if ($DB->record_exists('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
    $learner = $DB->get_record('user', [
        'username' => $username,
        'mnethostid' => $CFG->mnet_localhost_id,
    ], '*', MUST_EXIST);
} else {
    $learner = (object)[
        'auth' => 'manual',
        'confirmed' => 1,
        'mnethostid' => $CFG->mnet_localhost_id,
        'username' => $username,
        'password' => $password,
        'firstname' => 'Project',
        'lastname' => 'Learner',
        'email' => 'projectn-learner@example.invalid',
        'emailstop' => 0,
    ];
    $learner->id = user_create_user($learner);
}

// Iomad requires an explicit tenant membership and company-course assignment
// in addition to Moodle's normal manual enrolment.
$company = null;
if ($DB->get_manager()->table_exists('local_iomad_companies')) {
    $companyid = $DB->get_field('local_iomad_companies', 'id', ['shortname' => 'projectn_ci']);
    if ($companyid) {
        $company = new \local_iomad\company($companyid);
    } else {
        $company = \local_iomad\company::create_company((object)[
            'name' => 'Project N CI',
            'shortname' => 'projectn_ci',
            'city' => 'Test City',
            'country' => 'FR',
            'theme' => 'projectn',
            'lang' => 'en',
            'timezone' => '99',
            'templates' => [],
        ]);
    }
    $company->assign_user_to_company($learner->id);
    $company->add_course($course);
} else if ($DB->get_manager()->table_exists('company') &&
        $DB->get_manager()->table_exists('company_users')) {
    // Iomad 4.5 uses the legacy company schema and global classes.
    require_once($CFG->dirroot . '/local/iomad/lib/company.php');
    require_once($CFG->dirroot . '/local/iomad/lib/user.php');
    $companyid = $DB->get_field('company', 'id', ['shortname' => 'projectn_ci']);
    if (!$companyid) {
        $companyid = $DB->insert_record('company', (object)[
            'name' => 'Project N CI',
            'shortname' => 'projectn_ci',
            'city' => 'Test City',
            'country' => 'FR',
            'theme' => 'projectn',
            'category' => 1,
        ]);
    }
    $company = new \company($companyid);
    $company->assign_user_to_company($learner->id);
    $company->add_course($course);
}

if (!enrol_is_enabled('manual')) {
    throw new moodle_exception('manualenrolnotenabled');
}
$manual = enrol_get_plugin('manual');
$instances = enrol_get_instances($course->id, true);
$instance = null;
foreach ($instances as $candidate) {
    if ($candidate->enrol === 'manual') {
        $instance = $candidate;
        break;
    }
}
if (!$instance) {
    $instanceid = $manual->add_instance($course, ['status' => ENROL_INSTANCE_ENABLED]);
    if (!$instanceid) {
        throw new moodle_exception('cannotcreateinstance', 'enrol_manual');
    }
    $instance = $DB->get_record('enrol', ['id' => $instanceid], '*', MUST_EXIST);
}
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
$manual->enrol_user($instance, $learner->id, $studentrole->id, 0, 0, ENROL_USER_ACTIVE);
if ($company) {
    if (class_exists('local_iomad\\company_user')) {
        \local_iomad\company_user::enrol($learner, $course->id, $company->id, $studentrole->id);
    } else if (class_exists('company_user')) {
        \company_user::enrol($learner, $course->id, $company->id, $studentrole->id);
    }
}

// Add real course content so the learner course-view audit exercises native
// section and activity markup rather than an empty course shell.
$pageModuleId = $DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);
$hasPageActivity = $DB->record_exists('course_modules', [
    'course' => $course->id,
    'module' => $pageModuleId,
]);
if (!$hasPageActivity) {
    $visiblepage = (object)[
        'modulename' => 'page',
        'module' => $pageModuleId,
        'course' => $course->id,
        'section' => 1,
        'name' => 'Learner visible resource',
        'intro' => '',
        'introformat' => FORMAT_HTML,
        'content' => '<p>Visible learning content for responsive course-view testing.</p>',
        'contentformat' => FORMAT_HTML,
        'display' => RESOURCELIB_DISPLAY_AUTO,
        'printintro' => 0,
        'printlastmodified' => 1,
        'visible' => 1,
        'visibleoncoursepage' => 1,
        'groupmode' => 0,
        'groupingid' => 0,
        'completion' => COMPLETION_TRACKING_AUTOMATIC,
        'completionview' => 1,
        'completionexpected' => 0,
        'completionpassgrade' => 0,
        'availability' => null,
        'showdescription' => 0,
        'conditiongradegroup' => [],
        'conditionfieldgroup' => [],
        'conditioncompletiongroup' => [],
        'cmidnumber' => '',
    ];
    add_moduleinfo($visiblepage, $course);

    $hiddenpage = clone $visiblepage;
    $hiddenpage->name = 'Hidden learner resource';
    $hiddenpage->visible = 0;
    add_moduleinfo($hiddenpage, $course);
    rebuild_course_cache($course->id, true);
}

// Exercise the native weekly format as a second, genuinely enrolled learner course.
$weeklyshortname = 'projectn-learner-weekly-ci';
if ($DB->record_exists('course', ['shortname' => $weeklyshortname])) {
    $weeklycourse = $DB->get_record('course', ['shortname' => $weeklyshortname], '*', MUST_EXIST);
} else {
    $weeklycourse = create_course((object)[
        'category' => $categoryid,
        'fullname' => 'Project N weekly learner validation',
        'shortname' => $weeklyshortname,
        'format' => 'weeks',
        'numsections' => 2,
        'visible' => 1,
        'enablecompletion' => 1,
    ]);
}
if ($company) {
    $company->add_course($weeklycourse);
}
$weeklyinstanceid = null;
foreach (enrol_get_instances($weeklycourse->id, true) as $candidate) {
    if ($candidate->enrol === 'manual') {
        $weeklyinstanceid = $candidate->id;
        break;
    }
}
if (!$weeklyinstanceid) {
    $weeklyinstanceid = $manual->add_instance($weeklycourse, ['status' => ENROL_INSTANCE_ENABLED]);
}
$weeklyinstance = $DB->get_record('enrol', ['id' => $weeklyinstanceid], '*', MUST_EXIST);
$manual->enrol_user($weeklyinstance, $learner->id, $studentrole->id, 0, 0, ENROL_USER_ACTIVE);
if ($company) {
    if (class_exists('local_iomad\\company_user')) {
        \\local_iomad\\company_user::enrol($learner, $weeklycourse->id, $company->id, $studentrole->id);
    } else if (class_exists('company_user')) {
        \company_user::enrol($learner, $weeklycourse->id, $company->id, $studentrole->id);
    }
}
if (!$DB->record_exists('course_modules', ['course' => $weeklycourse->id, 'module' => $pageModuleId])) {
    $weeklypage = (object)[
        'modulename' => 'page',
        'module' => $pageModuleId,
        'course' => $weeklycourse->id,
        'section' => 1,
        'name' => 'Weekly format learner resource',
        'intro' => '',
        'introformat' => FORMAT_HTML,
        'content' => '<p>Visible learning content in the native weekly format.</p>',
        'contentformat' => FORMAT_HTML,
        'display' => RESOURCELIB_DISPLAY_AUTO,
        'printintro' => 0,
        'printlastmodified' => 1,
        'visible' => 1,
        'visibleoncoursepage' => 1,
        'groupmode' => 0,
        'groupingid' => 0,
        'completion' => COMPLETION_TRACKING_AUTOMATIC,
        'completionview' => 1,
        'completionexpected' => 0,
        'completionpassgrade' => 0,
        'availability' => null,
        'showdescription' => 0,
        'conditiongradegroup' => [],
        'conditionfieldgroup' => [],
        'conditioncompletiongroup' => [],
        'cmidnumber' => '',
    ];
    add_moduleinfo($weeklypage, $weeklycourse);
    rebuild_course_cache($weeklycourse->id, true);
}

$githubenv = getenv('GITHUB_ENV');
if ($githubenv) {
    file_put_contents($githubenv, "LEARNER_USERNAME={$username}\nLEARNER_PASSWORD={$password}\nLEARNER_COURSE_ID={$course->id}\nLEARNER_WEEKLY_COURSE_ID={$weeklycourse->id}\n", FILE_APPEND);
}
echo "Prepared learner {$username}, enrolled in course {$course->id}.\n";
