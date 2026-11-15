const { chromium } = require('playwright');

const required = [
    'BASE_URL',
    'LEARNER_USERNAME',
    'LEARNER_PASSWORD',
    'LEARNER_COURSE_ID',
    'LEARNER_WEEKLY_COURSE_ID',
    'LEARNER_SINGLEACTIVITY_COURSE_ID',
];
for (const name of required) {
    if (!process.env[name]) {
        throw new Error(name + ' is required for the learner course checks.');
    }
}

async function main() {
    const browser = await chromium.launch({headless: true});
    try {
        const page = await browser.newPage();
        await page.goto(new URL('/login/index.php', process.env.BASE_URL).toString(), {
            waitUntil: 'domcontentloaded',
        });
        await page.locator('#username').fill(process.env.LEARNER_USERNAME);
        await page.locator('#password').fill(process.env.LEARNER_PASSWORD);
        await Promise.all([
            page.waitForURL(url => !url.pathname.endsWith('/login/index.php'), {timeout: 30000}).catch(() => null),
            page.locator('#loginbtn').click(),
        ]);
        if (new URL(page.url()).pathname.endsWith('/login/index.php')) {
            throw new Error('The dedicated learner account could not log in.');
        }

        const courses = [
            {
                name: 'topics',
                id: process.env.LEARNER_COURSE_ID,
                visibleActivity: 'Learner visible resource',
                hiddenActivity: 'Hidden learner resource',
            },
            {
                name: 'weeks',
                id: process.env.LEARNER_WEEKLY_COURSE_ID,
                visibleActivity: 'Weekly format learner resource',
            },
        ];

        // The singleactivity format deliberately redirects the course URL to its
        // configured main activity. Do not require a conventional course-content
        // wrapper: Moodle owns this format's navigation and access behaviour.
        const singleActivityUrl = new URL('/course/view.php', process.env.BASE_URL);
        singleActivityUrl.searchParams.set('id', process.env.LEARNER_SINGLEACTIVITY_COURSE_ID);
        await page.goto(singleActivityUrl.toString(), {waitUntil: 'domcontentloaded'});
        await page.waitForURL(url => url.pathname === '/mod/page/view.php', {timeout: 15000});
        if (!(await page.getByText('Native single activity format content for learner testing.', {exact: false}).isVisible())) {
            throw new Error('The native singleactivity course did not open its configured Page activity.');
        }
        for (const width of [320, 390, 768, 1280]) {
            await page.setViewportSize({width, height: 844});
            await page.waitForTimeout(100);
            const dimensions = await page.evaluate(() => ({
                viewport: document.documentElement.clientWidth,
                document: document.documentElement.scrollWidth,
            }));
            if (dimensions.document > dimensions.viewport + 1) {
                throw new Error(
                    'Horizontal page overflow in singleactivity format at ' + width +
                    'px: ' + JSON.stringify(dimensions),
                );
            }
        }
        console.log('Learner singleactivity check passed (#' + process.env.LEARNER_SINGLEACTIVITY_COURSE_ID + ').');

        for (const course of courses) {
            const url = new URL('/course/view.php', process.env.BASE_URL);
            url.searchParams.set('id', course.id);
            await page.goto(url.toString(), {waitUntil: 'domcontentloaded'});

            const current = new URL(page.url());
            if (current.pathname.endsWith('/login/index.php') ||
                current.pathname !== '/course/view.php' ||
                current.searchParams.get('id') !== String(course.id)) {
                throw new Error('Learner was not allowed to open the enrolled ' + course.name + ' course.');
            }
            if (await page.locator('.course-content').count() !== 1) {
                throw new Error('The native course content container is missing for ' + course.name + '.');
            }

            const visible = page.locator('.activity-item').filter({hasText: course.visibleActivity});
            if (await visible.count() !== 1 || !(await visible.isVisible())) {
                throw new Error('The expected visible activity is missing from the ' + course.name + ' course.');
            }

            if (course.name === 'topics') {
                const unavailableSection = page.getByText('Section hidden by availability', {exact: true});
                const unavailableActivity = page.locator('.activity-item').filter({hasText: 'Activity in unavailable section'});
                if (await unavailableSection.count() !== 0 || await unavailableActivity.count() !== 0) {
                    throw new Error('A section/activity with an unmet availability condition is exposed to the learner.');
                }
                const availableSection = page.getByText('Section available by date', {exact: true});
                const availableActivity = page.locator('.activity-item').filter({hasText: 'Activity in available section'});
                if (await availableSection.count() !== 1 || !(await availableSection.isVisible()) ||
                    await availableActivity.count() !== 1 || !(await availableActivity.isVisible())) {
                    throw new Error('A section/activity whose availability condition is met is not visible to the learner.');
                }
            }

            if (course.hiddenActivity) {
                const hidden = page.locator('.activity-item').filter({hasText: course.hiddenActivity});
                if (await hidden.count() !== 0) {
                    throw new Error('A hidden activity is exposed to the learner in the ' + course.name + ' course.');
                }
            }

            for (const width of [320, 390, 768, 1280]) {
                await page.setViewportSize({width, height: 844});
                await page.waitForTimeout(100);
                const dimensions = await page.evaluate(() => ({
                    viewport: document.documentElement.clientWidth,
                    document: document.documentElement.scrollWidth,
                }));
                if (dimensions.document > dimensions.viewport + 1) {
                    throw new Error(
                        'Horizontal page overflow in ' + course.name + ' format at ' + width +
                        'px: ' + JSON.stringify(dimensions),
                    );
                }
            }
            console.log('Learner course checks passed: ' + course.name + ' (#' + course.id + ').');
        }
    } finally {
        await browser.close();
    }
}

main().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
