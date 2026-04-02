import { test, expect } from '@playwright/test';

// Plage d'IDs à tester (ajuste selon ta base)
const exerciseIds = Array.from({ length: 20 }, (_, i) => i + 1); // IDs 1 à 20
const baseUrl = 'http://localhost/moncoachscolaire/public/index.php?page=view_exercise&id=';

test.describe('Affichage universel des exercices', () => {
  for (const id of exerciseIds) {
    test(`Exercice ID ${id} - affichage complet`, async ({ page }) => {
      await page.goto(`${baseUrl}${id}`);

      // Vérifie la présence du titre
      const title = page.locator('h3.exercise-title').first();
      await expect(title).toBeVisible();

      // Vérifie la présence du badge matière (si présent)
      const badge = page.locator('.exercice-badge.exercise-subject');
      if (await badge.count() > 0) {
        await expect(badge.first()).toBeVisible();
      }

      // Vérifie contenu principal (sous-questions, contenu ou erreur)
      const subQ = page.locator('.sub-question-item');
      const mainContent = page.locator('.exercise-content');
      const errorMsg = page.locator('text=Exercice introuvable');

      const hasContent = (await subQ.count()) > 0 ||
                        (await mainContent.count()) > 0 ||
                        (await errorMsg.count()) > 0;
      expect(hasContent).toBeTruthy();

      // Screenshot pour debug
      await page.screenshot({
        path: `test-results/exercise_${id}.png`,
        fullPage: true
      });
    });
  }
});
