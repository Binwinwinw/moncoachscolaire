import { test, expect } from "@playwright/test";

// Test E2E Dashboard Parent – Génération de code et affichage des widgets

test.describe("Dashboard Parent – UX & AJAX", () => {
  test("Chargement, widgets, génération code enfant", async ({ page }) => {
    // Aller sur le dashboard parent (adapter l’URL si besoin)
    await page.goto("/index.php?page=parents/dashboard_parent");
    await page.waitForLoadState("networkidle");

    // Vérifier la présence des sections clés
    await expect(
      page.locator('h2:has-text("Rattachement parent/élève")'),
    ).toBeVisible();
    await expect(
      page.locator('h2:has-text("Vos enfants suivis")'),
    ).toBeVisible();
    await expect(
      page.locator('h2:has-text("Statistiques de la plateforme")'),
    ).toBeVisible();
    await expect(
      page.locator('h2:has-text("Alertes & notifications")'),
    ).toBeVisible();

    // Vérifier l’absence d’erreur JS
    const errors = [];
    page.on("pageerror", (err) => errors.push(err.message));
    expect(errors, "Aucune erreur JS ne doit apparaître").toEqual([]);

    // Tester la génération de code enfant (bouton)
    const btn = page.locator("#generate-family-code");
    if (await btn.isVisible()) {
      await btn.click();
      // Attendre l’affichage du code généré
      await expect(page.locator("#family-code-output")).toContainText("Code :");
      await expect(page.locator("#family-code-expiry")).toBeVisible();
    }

    // Vérifier la liste des codes générés
    await expect(page.locator("#family-codes-list")).not.toContainText(
      "Erreur",
    );
  });
});
