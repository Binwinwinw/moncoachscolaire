import { test, expect } from '@playwright/test';

test.describe('User Login → Exercise Submit', () => {
  test('demo user → view exercice 1 → submit réponse', async ({ page }) => {
    // 1. Login
    await page.goto('http://127.0.0.1:8081/public/index.php?page=login');
    await page.fill('#username', 'demo');
    await page.fill('#password', 'demo123');
    await page.click('button[type="submit"], .login-btn');
    await expect(page.locator('.user-menu, #profile-link')).toBeVisible();

    // 2. View exercice (ID 1 validé par test précédent)
    await page.goto('http://127.0.0.1:8081/public/index.php?page=view_exercise&id=1');
    await expect(page.locator('h3.exercise-title')).toBeVisible();

    // 3. Submit réponse (adapte selector réponse)
    await page.fill('.answer-input, textarea[name="reponse"]', 'Ma réponse test');
    await page.click('#submit-btn, .btn-submit, button[type="submit"]');
    await expect(page.locator('.success-msg, .alert-success, text=Soumis avec succès')).toBeVisible();

    // Screenshot
    await page.screenshot({ path: 'test-results/login-exercise-submit.png', fullPage: true });
  });
});
