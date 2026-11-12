const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

const baseUrl = process.env.BASE_URL;
const username = process.env.ADMIN_USERNAME;
const password = process.env.ADMIN_PASSWORD;
const platform = process.env.PLATFORM_NAME || 'platform';
const version = process.env.PLATFORM_VERSION || 'unknown';

if (!baseUrl || !username || !password) {
    throw new Error('BASE_URL, ADMIN_USERNAME and ADMIN_PASSWORD are required.');
}

const outputDirectory = path.join(process.cwd(), 'artifacts', 'axe');
fs.mkdirSync(outputDirectory, { recursive: true });

const tags = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice'];
const pagesToAudit = [
    { name: 'dashboard', path: '/my/' },
    { name: 'course-index', path: '/course/index.php' },
    // Course 1 is the site course created by the standard Moodle installer.
    // Auditing it exercises the native course-view shell on every reference platform.
    { name: 'course-view', path: '/course/view.php?id=1' },
];

async function main() {
    const browser = await chromium.launch({ headless: true });
    const results = [];
    const blockingViolations = [];

    try {
        const context = await browser.newContext();
        const page = await context.newPage();

        await page.goto(new URL('/login/index.php', baseUrl).toString(), { waitUntil: 'domcontentloaded' });
        for (const width of [320, 390, 768, 1280]) {
            await page.setViewportSize({ width: width, height: 844 });
            await auditPage(page, 'login', width, results, blockingViolations);
        }

        await page.locator('#username').fill(username);
        await page.locator('#password').fill(password);
        // Wait for the navigation triggered by the click: waiting for the DOM of the
        // current login page can return before the form is submitted, which reads as a
        // failed login.
        await Promise.all([
            page.waitForURL((url) => !url.pathname.endsWith('/login/index.php'), { timeout: 30000 }).catch(() => null),
            page.locator('#loginbtn').click(),
        ]);
        await page.waitForLoadState('domcontentloaded');

        if (new URL(page.url()).pathname.endsWith('/login/index.php')) {
            throw new Error('Moodle/Iomad administrator login failed.');
        }

        for (const target of pagesToAudit) {
            await page.goto(new URL(target.path, baseUrl).toString(), { waitUntil: 'domcontentloaded' });
            if (target.name === 'dashboard' && platform === 'Moodle' && version === '5.3') {
                await waitForMoodle53TimelineReady(page);
            }
            for (const width of [320, 390, 768, 1280]) {
                await page.setViewportSize({ width: width, height: 844 });
                await auditPage(page, target.name, width, results, blockingViolations);
            }
        }

        // Repeat learner-facing pages in a separate browser context.
        // This is a real student enrolled in a dedicated course, not an administrator.
        const learnerContext = await browser.newContext();
        try {
            const learnerPage = await learnerContext.newPage();
            await learnerPage.goto(new URL('/login/index.php', baseUrl).toString(), { waitUntil: 'domcontentloaded' });
            await learnerPage.locator('#username').fill(process.env.LEARNER_USERNAME);
            await learnerPage.locator('#password').fill(process.env.LEARNER_PASSWORD);
            await Promise.all([
                learnerPage.waitForURL((url) => !url.pathname.endsWith('/login/index.php'), { timeout: 30000 }).catch(() => null),
                learnerPage.locator('#loginbtn').click(),
            ]);
            await learnerPage.waitForLoadState('domcontentloaded');
            if (new URL(learnerPage.url()).pathname.endsWith('/login/index.php')) {
                throw new Error('Dedicated learner login failed.');
            }

            const learnerPages = [
                { name: 'learner-dashboard', path: '/my/' },
                { name: 'learner-course-index', path: '/course/index.php' },
                { name: 'learner-course-view', path: '/course/view.php?id=' + encodeURIComponent(process.env.LEARNER_COURSE_ID) },
            ];
            for (const target of learnerPages) {
                await learnerPage.goto(new URL(target.path, baseUrl).toString(), { waitUntil: 'domcontentloaded' });
                for (const width of [320, 390, 768, 1280]) {
                    await learnerPage.setViewportSize({ width: width, height: 844 });
                    await auditPage(learnerPage, target.name, width, results, blockingViolations);
                }
            }
        } finally {
            await learnerContext.close();
        }

        const report = {
            platform: platform,
            version: version,
            baseUrl: baseUrl,
            generatedAt: new Date().toISOString(),
            viewportWidths: [320, 390, 768, 1280],
            tags: tags,
            results: results,
            blockingViolations: blockingViolations,
        };

        fs.writeFileSync(
            path.join(outputDirectory, 'axe-' + platform.toLowerCase() + '-' + version + '.json'),
            JSON.stringify(report, null, 2),
        );

        if (blockingViolations.length > 0) {
            console.error('axe-core found ' + blockingViolations.length + ' violation(s) across the audited states.');
            for (const violation of blockingViolations) {
                const targets = violation.nodes.slice(0, 3).map(node => node.target.join(' ')).join(' | ');
                console.error(
                    '- ' + violation.id + ' (' + violation.impact + ') on ' + violation.page +
                    ' at ' + violation.width + ' px, ' + violation.nodes.length + ' node(s): ' + targets,
                );
            }
            process.exitCode = 1;
        } else {
            console.log('axe-core: no violations in the audited states.');
        }
    } finally {
        await browser.close();
    }
}

async function waitForMoodle53TimelineReady(page) {
    // Moodle 5.3 mounts the Timeline block as a React component. Its accessible
    // labels are fetched asynchronously, so domcontentloaded alone is not a
    // reliable signal that the component is ready for an accessibility audit.
    await page.waitForSelector('[data-region="timeline"]', { state: 'visible', timeout: 15000 });
    await page.waitForFunction(() => {
        const timeline = document.querySelector('[data-region="timeline"]');
        if (!timeline) {
            return false;
        }

        const dayFilter = timeline.querySelector('button[aria-controls="menudayfilter"]');
        const viewSelector = timeline.querySelector('button[aria-controls="menusortby"]');
        const searchInput = timeline.querySelector('input[data-action="search"]');
        const searchLabel = searchInput && [...timeline.querySelectorAll('label')]
            .find(label => label.htmlFor === searchInput.id);

        return Boolean(
            dayFilter?.getAttribute('aria-label')?.trim() &&
            viewSelector?.getAttribute('aria-label')?.trim() &&
            searchInput?.getAttribute('placeholder')?.trim() &&
            searchLabel?.textContent?.trim()
        );
    }, null, { timeout: 15000 });
}

async function auditPage(page, pageName, width, results, blockingViolations) {
    await page.addScriptTag({ path: require.resolve('axe-core/axe.min.js') });
    const report = await page.evaluate(async selectedTags => {
        return window.axe.run(document, {
            runOnly: { type: 'tag', values: selectedTags },
        });
    }, tags);

    const summary = {
        page: pageName,
        width: width,
        url: page.url(),
        passes: report.passes.length,
        incomplete: report.incomplete.length,
        violations: report.violations.map(violation => ({
            id: violation.id,
            impact: violation.impact,
            description: violation.description,
            help: violation.help,
            helpUrl: violation.helpUrl,
            nodes: violation.nodes.map(node => ({
                target: node.target,
                html: node.html,
                failureSummary: node.failureSummary,
                any: node.any,
                all: node.all,
                none: node.none,
            })),
        })),
        diagnostics: await page.evaluate(() => {
            const describe = element => {
                const style = window.getComputedStyle(element);
                const rect = element.getBoundingClientRect();
                return {
                    html: element.outerHTML,
                    rect: {
                        x: rect.x,
                        y: rect.y,
                        width: rect.width,
                        height: rect.height,
                    },
                    display: style.display,
                    position: style.position,
                    minWidth: style.minWidth,
                    minHeight: style.minHeight,
                    width: style.width,
                    height: style.height,
                    padding: style.padding,
                    margin: style.margin,
                    lineHeight: style.lineHeight,
                    fontSize: style.fontSize,
                };
            };
            return {
                projectnNavigation: [...document.querySelectorAll('.projectn-global-navigation')].map(describe),
                projectnLinks: [...document.querySelectorAll('.projectn-global-navigation__link')].map(describe),
                timelineButtons: [...document.querySelectorAll(
                    'button[aria-label="Filter timeline by date"], button[aria-label="Sort timeline items"]'
                )].map(describe),
                moremenus: [...document.querySelectorAll('nav.moremenu.navigation')].map(element => ({
                    html: element.outerHTML,
                    ariaLabel: element.getAttribute('aria-label'),
                    listRole: element.querySelector('ul')?.getAttribute('role') || null,
                    listClass: element.querySelector('ul')?.className || null,
                })),
                stylesheets: [...document.styleSheets].map(sheet => sheet.href).filter(Boolean),
            };
        }),
    };
    results.push(summary);

    for (const violation of summary.violations) {
        {
            blockingViolations.push({
                page: pageName,
                width: width,
                id: violation.id,
                impact: violation.impact,
                description: violation.description,
                nodes: violation.nodes,
            });
            const safeName = pageName.replace(/[^a-z0-9-]/gi, '-');
            await page.screenshot({
                path: path.join(outputDirectory, 'failure-' + safeName + '-' + width + '-' + violation.id + '.png'),
                fullPage: true,
            });
        }
    }
}

main().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
