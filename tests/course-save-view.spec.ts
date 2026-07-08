import { test, expect } from "@playwright/test";

test.describe("Cours IA : sauvegarde puis ouverture du cours enregistré", () => {
  test("sauvegarde un cours généré puis ouvre le cours réel", async ({
    page,
  }) => {
    await page.route(
      "**/index.php?page=api/ia/generate_cours",
      async (route) => {
        const request = route.request();
        const rawBody = request.postData();
        const body = rawBody ? JSON.parse(rawBody) : {};
        await route.fulfill({
          status: 200,
          contentType: "application/json",
          body: JSON.stringify({
            success: true,
            cours: {
              title: "Cours IA de test",
              sections: [
                { title: "Introduction", content: "Contenu de démonstration." },
              ],
            },
            cours_html:
              '<div class="course-preview"><h3>Cours IA de test</h3><p>Prévisualisation de test</p></div>',
            level: body.niveau || "6eme",
            matiere: body.matiere || "Mathématiques",
          }),
        });
      },
    );

    await page.route(
      "**/index.php?page=api/ia/save_generated_cours",
      async (route) => {
        await route.fulfill({
          status: 200,
          contentType: "application/json",
          body: JSON.stringify({
            success: true,
            course_id: 999,
            course_url: "/index.php?page=view_course&id=999",
            message: "Cours sauvegardé dans la bibliothèque.",
          }),
        });
      },
    );

    await page.route("**/index.php?page=view_course&id=999", async (route) => {
      await route.fulfill({
        status: 200,
        contentType: "text/html",
        body: "<html><body><h1>Cours enregistré</h1><p>Le cours réel a bien été ouvert.</p></body></html>",
      });
    });

    await page.goto("http://127.0.0.1:8081/index.php?page=login");
    await page.waitForSelector("#username", { timeout: 10000 });
    await page.fill("#username", "demo");
    await page.fill("#password", "demo");
    await page.locator('button[type="submit"]').first().click();

    await page.goto("http://127.0.0.1:8081/index.php?page=cours");
    await expect(page.locator("#btn-quiz-ia")).toBeVisible();
    await page.locator("#btn-quiz-ia").click();

    await page.selectOption("#quiz-niveau", "6eme");
    await page.selectOption("#quiz-matiere", "Mathématiques");
    await page.fill("#quiz-type", "Fractions");
    await page.locator('#form-quiz-ia button[type="submit"]').click();

    await expect(page.locator("#quiz-ia-result")).toBeVisible();
    await expect(page.locator("#btn-save-generated-cours")).toBeVisible();
    await page.locator("#btn-save-generated-cours").click();

    await expect(page.locator("#btn-save-generated-cours")).toContainText(
      "Voir le cours complet",
    );
    await page.locator("#btn-save-generated-cours").click();

    await expect(page).toHaveURL(/view_course&id=999/);
    await expect(page.locator("h1")).toContainText(/cours enregistr/i);
  });
});
