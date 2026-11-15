const { chromium } = require('playwright');

const required = [
    'BASE_URL',
    'LEARNER_USERNAME',
    'LEARNER_PASSWORD',
    'LEARNER_COURSE_ID',
    'LEARNER_WEEKLY_COURSE_ID',
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
