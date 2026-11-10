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
        await page.locator('#loginbtn').click();
        await page.waitForLoadState('domcontentloaded');

        if (new URL(page.url()).pathname.endsWith('/login/index.php')) {
            throw new Error('Moodle/Iomad administrator login failed.');
        }

        for (const target of pagesToAudit) {
            await page.goto(new URL(target.path, baseUrl).toString(), { waitUntil: 'domcontentloaded' });
            for (const width of [320, 390, 768, 1280]) {
                await page.setViewportSize({ width: width, height: 844 });
                await auditPage(page, target.name, width, results, blockingViolations);
            }
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
            console.error('axe-core found ' + blockingViolations.length + ' serious/critical violation(s).');
            process.exitCode = 1;
        } else {
            console.log('axe-core: no serious or critical violations in the audited states.');
        }
    } finally {
        await browser.close();
    }
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
                failureSummary: node.failureSummary,
            })),
        })),
    };
    results.push(summary);

    for (const violation of summary.violations) {
        if (violation.impact === 'critical' || violation.impact === 'serious') {
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
