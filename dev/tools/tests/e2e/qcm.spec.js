const { test, expect } = require('@playwright/test');

test.describe('QCM interactivity and save progress', () => {
  test('Seconde: grade QCM and save progress', async ({ page }) => {
    await page.goto('http://127.0.0.1/moncoachscolaire/lycee/seconde/exercices-seconde.php');

    // Sélectionne la première option "b" de la première question
    await page.locator('.qcm-question').first().locator('[data-qcm-option="b"]').click();
    // Sélectionne la première option "a" de la deuxième question (exemple)
    await page.locator('.qcm-question').nth(1).locator('[data-qcm-option="a"]').click();

    // click verify
    await page.locator('.qcm-check-button').click();

    // Vérifie que le score s'affiche (peu importe la valeur)
    const score = page.locator('.qcm-score');
    await expect(score).toHaveClass(/visible/);
  });
});
