import { test, expect } from '@playwright/test';

test.describe('Diagnostic quiz source path', () => {
  test('quiz API serves file from src/data/quiz', async ({ request }) => {
    const response = await request.get('src/api/quiz.php?id=29');
    expect(response.ok()).toBeTruthy();

    const payload = await response.json();
    expect(payload?.contents?._source).toContain('src/data/quiz/29.json');
    expect(Array.isArray(payload?.quiz?.questions)).toBeTruthy();
    expect(payload.quiz.questions.length).toBeGreaterThan(0);
    expect(payload?.contents?._source).not.toContain('public/quiz');
  });

  test('diagnostic page starts quiz without public/quiz path', async ({ page }) => {
    await page.goto('?page=diagnostic&level=4eme&subject=Anglais');

    const firstCard = page.locator('button.quiz-card').first();
    await expect(firstCard).toBeVisible({ timeout: 15000 });

    await firstCard.click();

    await expect(page.locator('.question').first()).toBeVisible({ timeout: 15000 });
    await expect(page.locator('text=public/quiz')).toHaveCount(0);
  });

  test('diagnostic list is paginated for large quiz pools', async ({ page }) => {
    await page.goto('?page=diagnostic&level=4eme&subject=Math%C3%A9matiques');

    const cards = page.locator('button.quiz-card');
    await expect(cards.first()).toBeVisible({ timeout: 15000 });
    const cardCount = await cards.count();
    expect(cardCount).toBeLessThanOrEqual(12);

    const page2 = page.locator('button[data-quiz-page="2"]');
    if (await page2.count()) {
      await page2.click();
      await expect(page.locator('button[data-quiz-page="2"]')).toHaveClass(/bg-blue-600/);
      await expect(cards.first()).toBeVisible({ timeout: 15000 });
    }
  });
});
