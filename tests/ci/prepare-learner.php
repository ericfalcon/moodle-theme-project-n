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

$shortname = 'projectn-learner-ci';
if ($DB->record_exists('course', ['shortname' => $shortname])) {
    $course = $DB->get_record('course', ['shortname' => $shortname], '*', MUST_EXIST);
} else {
    $categoryid = $DB->get_field('course_categories', 'id', ['id' => 1], MUST_EXIST);
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

$githubenv = getenv('GITHUB_ENV');
if ($githubenv) {
    file_put_contents($githubenv, "LEARNER_USERNAME={$username}\nLEARNER_PASSWORD={$password}\nLEARNER_COURSE_ID={$course->id}\n", FILE_APPEND);
}
echo "Prepared learner {$username}, enrolled in course {$course->id}.\n";
