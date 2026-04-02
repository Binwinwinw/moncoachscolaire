// Playwright config for MonCoachScolaire E2E tests
const { defineConfig } = require("@playwright/test");

module.exports = defineConfig({
    testDir: ".",
    timeout: 30 * 1000,
    expect: { timeout: 5000 },
    use: {
        // Allow overriding the base URL in CI via PLAYWRIGHT_BASE_URL (e.g. http://127.0.0.1:8080)
        baseURL: "http://localhost/moncoachscolaire/",
        headless: true,
        viewport: { width: 1280, height: 800 },
        ignoreHTTPSErrors: true,
        video: "retain-on-failure",
    },
    projects: [{ name: "chromium", use: { browserName: "chromium" } }],
    // Inclure tous les tests *.spec.ts dans tests/ et dev/tools/tests/e2e
    testMatch: [
        "dev/tools/tests/e2e/**/*.spec.{js,ts}",
        "tests/**/*.spec.{js,ts}",
    ],
});
