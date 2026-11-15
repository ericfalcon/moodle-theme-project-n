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
    const platformCoreExclusions = [];

    try {
        const context = await browser.newContext();
        const page = await context.newPage();

        await page.goto(new URL('/login/index.php', baseUrl).toString(), { waitUntil: 'domcontentloaded' });
        for (const width of [320, 390, 768, 1280]) {
            await page.setViewportSize({ width: width, height: 844 });
            await auditPage(page, 'login', width, results, blockingViolations, platformCoreExclusions);
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
                await verifyNavigationAndUserMenu(page, width, target.name);
                if (target.name === 'dashboard' && platform === 'Moodle' && version === '5.3') {
                    await waitForMoodle53TimelineReady(page);
                }
                await auditPage(page, target.name, width, results, blockingViolations, platformCoreExclusions);
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
                { name: 'learner-weekly-course-view', path: '/course/view.php?id=' + encodeURIComponent(process.env.LEARNER_WEEKLY_COURSE_ID) },
            ];
            for (const target of learnerPages) {
                await learnerPage.goto(new URL(target.path, baseUrl).toString(), { waitUntil: 'domcontentloaded' });
                if (target.name === 'learner-course-view') {
                    const currentUrl = new URL(learnerPage.url());
                    const courseContentCount = await learnerPage.locator('.course-content').count();
                    if (!currentUrl.pathname.endsWith('/course/view.php') || courseContentCount === 0) {
                        throw new Error('Learner course view did not load the enrolled course page.');
                    }
                }
                for (const width of [320, 390, 768, 1280]) {
                    await learnerPage.setViewportSize({ width: width, height: 844 });
                    await verifyNavigationAndUserMenu(learnerPage, width, target.name);
                    if (target.name === 'learner-dashboard' && platform === 'Moodle' && version === '5.3') {
                        await waitForMoodle53TimelineReady(learnerPage);
                    }
                    await auditPage(learnerPage, target.name, width, results, blockingViolations, platformCoreExclusions);
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
            platformCoreExclusions: platformCoreExclusions,
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
            console.log('axe-core: no Project N/theme violations. ' + platformCoreExclusions.length + ' known platform-core violation instance(s) are reported separately.');
        }
    } catch (error) {
        // Preserve the partial axe report when a browser interaction aborts the run.
        // Without this, only screenshots survive and the violations already collected
        // before the failing interaction cannot be inspected.
        const partialReport = {
            platform: platform,
            version: version,
            baseUrl: baseUrl,
            generatedAt: new Date().toISOString(),
            viewportWidths: [320, 390, 768, 1280],
            tags: tags,
            results: results,
            blockingViolations: blockingViolations,
            platformCoreExclusions: platformCoreExclusions,
            fatalError: error.message,
        };
        fs.writeFileSync(
            path.join(outputDirectory, 'axe-' + platform.toLowerCase() + '-' + version + '-partial.json'),
            JSON.stringify(partialReport, null, 2),
        );
        throw error;
    } finally {
        await browser.close();
    }
}

async function verifyNavigationAndUserMenu(page, width, pageName) {
    const navigation = page.locator('.projectn-global-navigation');
    if (await navigation.count() !== 1) {
        throw new Error('Project N global navigation is missing or duplicated on ' + pageName + '.');
    }

    const navigationLinks = navigation.locator('.projectn-global-navigation__link');
    if (await navigationLinks.count() !== 4) {
        throw new Error('Project N mobile toolbar must expose four destinations on ' + pageName + '.');
    }

    const bounds = await navigation.boundingBox();
    if (!bounds || bounds.x < 0 || bounds.x + bounds.width > width + 1) {
        throw new Error('Project N navigation exceeds the viewport on ' + pageName + ' at ' + width + ' px.');
    }

    if (width < 768 && (await navigation.evaluate(element => getComputedStyle(element).position)) !== 'fixed') {
        throw new Error('Project N mobile navigation is not fixed to the viewport bottom.');
    }

    await page.waitForFunction(() => {
        const menus = [...document.querySelectorAll('nav.moremenu')];
        return menus.every(menu => menu.getAttribute('aria-label')?.trim() || menu.getAttribute('aria-labelledby'));
    }, null, { timeout: 5000 });

    const ariaRepairs = await page.evaluate(() => ({
        unnamedMoremenus: [...document.querySelectorAll('nav.moremenu')]
            .filter(menu => !menu.getAttribute('aria-label')?.trim() && !menu.getAttribute('aria-labelledby'))
            .length,
        invalidAriaHidden: document.querySelectorAll('[aria-hidden="1"]').length,
        brokenAriaControls: [...document.querySelectorAll('[aria-controls]')].filter(element =>
            element.getAttribute('aria-controls').split(/\s+/).some(id => !document.getElementById(id))
        ).length,
    }));
    if (ariaRepairs.unnamedMoremenus || ariaRepairs.invalidAriaHidden || ariaRepairs.brokenAriaControls) {
        throw new Error('Project N ARIA repairs are incomplete on ' + pageName + ': ' + JSON.stringify(ariaRepairs));
    }

    const userMenuToggle = page.locator('#user-menu-toggle').first();
    if (await userMenuToggle.count() !== 1) {
        throw new Error('Native Moodle user-menu toggle is missing on ' + pageName + '.');
    }
    await page.waitForFunction(() => {
        const toggle = document.querySelector('#user-menu-toggle');
        const visibleText = toggle?.querySelector('.userbutton')?.innerText?.replace(/\s+/g, ' ').trim();
        const accessibleName = toggle?.getAttribute('aria-label') || '';
        return !visibleText || accessibleName.toLocaleLowerCase().includes(visibleText.toLocaleLowerCase());
    }, null, { timeout: 5000 });

    const userMenuName = await userMenuToggle.evaluate(element => ({
        visibleText: element.querySelector('.userbutton')?.innerText?.replace(/\s+/g, ' ').trim() || '',
        accessibleName: element.getAttribute('aria-label') || '',
    }));
    if (userMenuName.visibleText &&
        !userMenuName.accessibleName.toLocaleLowerCase().includes(userMenuName.visibleText.toLocaleLowerCase())) {
        throw new Error('User-menu accessible name omits its visible label on ' + pageName +
            ' at ' + width + ' px: ' + JSON.stringify(userMenuName));
    }

    // Boost loads its Bootstrap integration asynchronously from the native drawers
    // template. DOMContentLoaded and the ARIA repair hook do not guarantee that the
    // dropdown data API is ready. Wait for both native AMD modules before interacting.
    await page.waitForFunction(() => {
        const requirejs = window.requirejs;
        return Boolean(requirejs && requirejs.defined('theme_boost/loader') &&
            requirejs.defined('core/usermenu'));
    }, null, { timeout: 15000 }).catch(async () => {
        const state = await page.evaluate(() => ({
            boostLoaderDefined: window.requirejs?.defined?.('theme_boost/loader') ?? null,
            userMenuDefined: window.requirejs?.defined?.('core/usermenu') ?? null,
            jqueryDefined: window.requirejs?.defined?.('jquery') ?? null,
            bootstrapDefined: window.requirejs?.defined?.('bootstrap') ?? null,
        }));
        throw new Error('Native Boost/user-menu AMD modules are not ready on ' + pageName +
            ' at ' + width + ' px: ' + JSON.stringify(state));
    });

    const runtime = await page.evaluate(() => ({
        bootstrapGlobal: typeof window.bootstrap,
        bootstrapDropdownVersion: window.bootstrap?.Dropdown?.VERSION || null,
        jqueryVersion: window.jQuery?.fn?.jquery || null,
        jqueryDropdown: typeof window.jQuery?.fn?.dropdown,
        requirejs: typeof window.requirejs,
        userMenuModuleDefined: window.requirejs?.defined?.('core/usermenu') ?? null,
        boostLoaderDefined: window.requirejs?.defined?.('theme_boost/loader') ?? null,
        bootstrapModuleDefined: window.requirejs?.defined?.('bootstrap') ?? null,
        scripts: [...document.scripts].map(script => script.src).filter(Boolean),
    }));
    const dropdownEvents = [];
    await page.evaluate(() => {
        const toggle = document.querySelector('#user-menu-toggle');
        const parent = toggle?.closest('.dropdown');
        if (!parent) {
            return;
        }
        parent.addEventListener('show.bs.dropdown', () => {
            window.__projectnDropdownEvents = (window.__projectnDropdownEvents || []).concat('native-show');
        });
        parent.addEventListener('shown.bs.dropdown', () => {
            window.__projectnDropdownEvents = (window.__projectnDropdownEvents || []).concat('native-shown');
        });
        if (window.jQuery) {
            window.jQuery(parent).on('show.bs.dropdown shown.bs.dropdown', event => {
                window.__projectnDropdownEvents = (window.__projectnDropdownEvents || []).concat('jquery-' + event.type);
            });
        }
    });
    await userMenuToggle.click();
    await page.waitForFunction(() => {
        const menu = document.querySelector('#user-action-menu');
        if (!menu) {
            return false;
        }
        const style = getComputedStyle(menu);
        const rect = menu.getBoundingClientRect();
        return menu.classList.contains('show') && style.display !== 'none' && rect.width > 0 && rect.height > 0;
    }, null, { timeout: 5000 }).catch(async () => {
        const diagnostic = await page.locator('#user-menu-toggle').evaluate(element => ({
            html: element.outerHTML,
            expanded: element.getAttribute('aria-expanded'),
            menu: document.querySelector('#user-action-menu')?.outerHTML || null,
            menuDisplay: document.querySelector('#user-action-menu') ? getComputedStyle(document.querySelector('#user-action-menu')).display : null,
            dropdown: element.closest('.dropdown')?.outerHTML || null,
            dropdownEvents: window.__projectnDropdownEvents || [],
        }));
        throw new Error('Native Moodle user menu did not open on ' + pageName + ' at ' + width +
            ' px. Runtime: ' + JSON.stringify(runtime) + '. Diagnostic: ' + JSON.stringify(diagnostic));
    });

    // Close the menu again so the accessibility audit starts from a stable page state.
    await userMenuToggle.click();
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

async function auditPage(page, pageName, width, results, blockingViolations, platformCoreExclusions) {
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
        const exclusion = getPlatformCoreExclusion(pageName, violation);
        if (exclusion) {
            platformCoreExclusions.push({
                page: pageName,
                width: width,
                id: violation.id,
                impact: violation.impact,
                rationale: exclusion,
                nodes: violation.nodes,
            });
            continue;
        }

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

function getPlatformCoreExclusion(pageName, violation) {
    const targets = violation.nodes.flatMap(node => node.target);
    if ((pageName === 'course-index' || pageName === 'learner-course-index') &&
            violation.id === 'heading-order' && targets.some(target => target.endsWith('.coursename'))) {
        return 'Moodle/Iomad core course-index template uses an h3 course title without the preceding heading level; this page is not rendered or templated by Project N.';
    }

    if (platform === 'Iomad' && pageName === 'learner-course-index' &&
            violation.id === 'landmark-one-main' && targets.includes('html')) {
        return 'Iomad core course-index response lacks the single main landmark; this is outside the theme-owned course dashboard and course view.';
    }

    if (platform === 'Iomad' && pageName === 'learner-course-index' &&
            violation.id === 'region' && targets.includes('#page')) {
        return 'Iomad core course-index response has page content outside a landmark; this is outside the theme-owned course dashboard and course view.';
    }

    if (platform === 'Iomad' && pageName === 'course-index' &&
            violation.id === 'heading-order' &&
            targets.includes('div[data-categoryid="2"] > .info > .categoryname.aabtn')) {
        return 'Iomad core course-index category heading skips a heading level; Project N does not render this native category listing.';
    }

    if (platform === 'Iomad' && pageName === 'learner-course-index' &&
            violation.id === 'heading-order' &&
            targets.includes('div[data-categoryid="2"] > .info > .categoryname.aabtn')) {
        return 'Iomad core course-index category heading skips a heading level; Project N does not render this native category listing.';
    }

    if (platform === 'Iomad' && pageName === 'dashboard' &&
            violation.id === 'aria-required-children' &&
            violation.nodes.every(node => node.target.includes('.nav-tabs') &&
                node.html.includes('<ul class="nav nav-tabs" role="tablist">'))) {
        return 'Iomad core company-administration dashboard tab list uses an incomplete ARIA tab pattern; this is not the Project N learner dashboard.';
    }

    if (platform === 'Iomad' && pageName === 'dashboard' &&
            violation.id === 'aria-required-parent' &&
            violation.nodes.every(node => (node.html.includes('data-bs-toggle="tab"') || node.html.includes('data-toggle="tab"')) &&
                node.html.includes('role="tab"') &&
                node.target.some(target => /^a\[href\$="#(?:CompanyAdmin|UserAdmin|CourseAdmin|LicenseAdmin|CompetencyAdmin|ECommerceAdmin|MicrolearningAdmin|Reports)"\]$/.test(target)))) {
        return 'Iomad core company-administration dashboard tab links are not wrapped in the required tablist structure; this is not the Project N learner dashboard.';
    }

    if (platform === 'Iomad' && pageName === 'dashboard' &&
            violation.id === 'listitem' &&
            violation.nodes.every(node => node.html.includes('<li class="nav-item">') &&
                node.target.some(target => target.includes('nav-item')))) {
        return 'Iomad core company-administration dashboard tab list has list-item semantics inconsistent with its ARIA tab role.';
    }

    if (platform === 'Iomad' && pageName === 'dashboard' &&
            violation.id === 'aria-allowed-role' &&
            violation.nodes.every(node => node.html.includes('role="combobox"') &&
                node.target.some(target => target.startsWith('#form_autocomplete_input-')))) {
        return 'Iomad core company-administration search autocomplete exposes a role combination flagged by axe-core; it is outside Project N.';
    }

    if (platform === 'Iomad' && pageName === 'dashboard' &&
            violation.id === 'color-contrast' &&
            violation.nodes.every(node => node.target.includes('a[href$="#CompanyAdmin"]') &&
                node.html.includes('data-bs-toggle="tab"'))) {
        return 'Iomad core company-administration tab colour contrast is outside the Project N learner interface.';
    }

    if (platform === 'Moodle' && version === '5.3' && pageName === 'learner-dashboard' &&
            violation.id === 'label-content-name-mismatch' &&
            ['#groupingdropdown', '#sortingdropdown', '#displaydropdown'].some(target => targets.includes(target))) {
        return 'Moodle 5.3 block_myoverview core templates set aria-label text that does not include the visible dropdown label; Project N does not override this native block.';
    }

    return null;
}

main().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
