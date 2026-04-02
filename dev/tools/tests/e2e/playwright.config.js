// Playwright config for MonCoachScolaire E2E tests
// Assumes a web server is available at http://localhost/moncoachscolaire/public/index.php?page=college%2F6eme%2Fexercices-6eme
const { defineConfig, devices } = require("@playwright/test");

module.exports = defineConfig({
  testDir: ".",
  timeout: 30 * 1000,
  expect: { timeout: 5000 },
  fullyParallel: true,
  reporter: [["list"], ["html", { outputFolder: "playwright-report" }]],
  use: {
    baseURL: "http://localhost/moncoachscolaire/public/index.php?page=college%2F6eme%2Fexercices-6eme",
    headless: true,
    viewport: { width: 1280, height: 800 },
    actionTimeout: 5000,
    ignoreHTTPSErrors: true,
  },
  projects: [
    { name: "chromium", use: { ...devices["Desktop Chrome"] } },
    {
      name: "Mobile Chrome",
      use: {
        browserName: "chromium",
        ...devices["iPhone 12"],
        viewport: { width: 390, height: 844 },
      },
    },
  ],
});
