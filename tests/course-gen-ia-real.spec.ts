import { test, expect } from "@playwright/test";

test.describe("Cours IA réel : génération avec admin1ere", () => {
  test("se connecte avec admin1ere, génère un cours Philosophie et affiche le résultat", async ({
    page,
  }) => {
    const baseUrl = "http://127.0.0.1:8081";

    await page.goto(`${baseUrl}/index.php?page=login`);
    await expect(page.locator("#username")).toBeVisible({ timeout: 15000 });
    await page.fill("#username", "admin1ere");
    await page.fill("#password", "admin123");
    await Promise.all([
      page.waitForNavigation({ waitUntil: "domcontentloaded", timeout: 20000 }),
      page.locator('button[type="submit"]').first().click(),
    ]);

    await page.goto(`${baseUrl}/index.php?page=cours`);
    await expect(page.locator("#btn-quiz-ia")).toBeVisible({ timeout: 20000 });
    await page.click("#btn-quiz-ia");

    await expect(page.locator("#modal-quiz-ia")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("#quiz-matiere")).toBeVisible({ timeout: 10000 });
    await page.selectOption("#quiz-matiere", "Philosophie");
    await page.fill("#quiz-theme", "Les pouvoirs de la parole");

    await Promise.all([
      page.waitForResponse(
        (response) =>
          response.url().includes("index.php?page=api/ia/generate_cours") &&
          response.status() === 200,
      ),
      page.locator("#form-quiz-ia button[type='submit']").click(),
    ]);

    const resultLocator = page.locator("#quiz-ia-result");
    await expect(resultLocator).toBeVisible({ timeout: 30000 });

    const generatedText = await resultLocator.textContent();
    test.slow();
    expect(generatedText).toBeTruthy();
    expect(generatedText?.length ?? 0).toBeGreaterThan(20);

    if (generatedText?.includes("Erreur")) {
      console.log("Course IA a renvoyé une erreur :", generatedText.trim());
    }
  });
});
