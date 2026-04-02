import { test, expect } from '@playwright/test';

// Plage d'IDs à tester (ajuste selon ta base)
const exerciseIds = Array.from({ length: 20 }, (_, i) => i + 1); // IDs 1 à 20

const baseUrl = '/index.php?page=view_exercise&id=';

test.describe('Affichage universel des exercices', () => {
  for (const id of exerciseIds) {
    test(`Exercice ID ${id} - affichage`, async ({ page }) => {
      await page.goto(`${baseUrl}${id}`);
      // Vérifie la présence du titre
      const title = await page.locator('h1').first();
      await expect(title).toBeVisible();
      // Vérifie la présence du badge matière (si présent)
      const badge = page.locator('.badge-matiere, .badge-matière');
      if (await badge.count() > 0) {
        await expect(badge).toBeVisible();
      }
      // Vérifie la présence d'au moins une sous-question ou du fallback
      const subQ = page.locator('.sub-question, .sous-question');
      const fallback = page.locator('.exercise-fallback, .exercice-fallback');
      expect((await subQ.count()) > 0 || (await fallback.count()) > 0).toBeTruthy();
      // Prend une capture d'écran pour vérification visuelle
      await page.screenshot({ path: `test-results/exercise_${id}.png`, fullPage: true });
    });
  }
});
