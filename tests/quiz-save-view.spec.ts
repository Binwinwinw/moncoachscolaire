import { test, expect } from "@playwright/test";

test.describe("Exercices IA : génération et sauvegarde", () => {
  test("génère un exercice IA puis le sauvegarde et ouvre l'exercice enregistré", async ({
    page,
  }) => {
    await page.route(
      "**/index.php?page=api/ia/generate_quiz",
      async (route) => {
        const request = route.request();
        const requestBody = request.postDataJSON();

        await route.fulfill({
          status: 200,
          contentType: "application/json",
          body: JSON.stringify({
            success: true,
            quiz_html:
              '<div class="qcm-exercise" data-questions="[]"><h3>Exercice IA de test</h3></div>',
            questions: [
              {
                question: "Quelle est la capitale de la France ?",
                choices: [
                  { value: "a", label: "Paris" },
                  { value: "b", label: "Lyon" },
                  { value: "c", label: "Marseille" },
                  { value: "d", label: "Bordeaux" },
                ],
                correct: "a",
              },
            ],
            subject: requestBody?.matiere || "Mathématiques",
            level: requestBody?.niveau || "6eme",
          }),
        });
      },
    );

    await page.route(
      "**/index.php?page=api/ia/save_generated_quiz",
      async (route) => {
        await route.fulfill({
          status: 200,
          contentType: "application/json",
          body: JSON.stringify({
            success: true,
            message: "Quiz sauvegardé dans la bibliothèque.",
            exercise_url:
              "/moncoachscolaire/index.php?page=view_exercise&id=999",
            data: {
              exercise_ids: [999],
              revision_id: 123,
              exercise_url:
                "/moncoachscolaire/index.php?page=view_exercise&id=999",
            },
          }),
        });
      },
    );

    await page.route("**/index.php?page=view_exercise*", async (route) => {
      await route.fulfill({
        status: 200,
        contentType: "text/html",
        body: `<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Exercice IA sauvegardé</title></head><body><h1>Exercice IA de test</h1><p>Contenu de l'exercice IA sauvegardé.</p></body></html>`,
      });
    });

    await page.goto("http://127.0.0.1:8081/index.php?page=login");
    await page.fill("#username", "admin6eme");
    await page.fill("#password", "admin123");
    await Promise.all([
      page.waitForNavigation({ waitUntil: "domcontentloaded" }),
      page.locator('button[type="submit"]').first().click(),
    ]);

    await page.goto("http://127.0.0.1:8081/index.php?page=system/exercices");
    await expect(page.locator("#btn-quiz-ia")).toBeVisible({ timeout: 10000 });
    await page.click("#btn-quiz-ia");

    await expect(page.locator("#modal-quiz-ia")).toBeVisible({
      timeout: 10000,
    });
    await page.selectOption("#quiz-matiere", "Mathématiques");
    await page.fill("#quiz-theme", "Fonctions linéaires");
    await page.locator("#form-quiz-ia button[type='submit']").click();

    await expect(page.locator("#quiz-ia-result")).toBeVisible({
      timeout: 10000,
    });
    await expect(page.locator("#btn-save-generated-quiz")).toBeVisible();

    await page.click("#btn-save-generated-quiz");
    await expect(page.locator("#btn-save-generated-quiz")).toHaveText(
      /Voir l’exercice IA/,
    );

    await page.click("#btn-save-generated-quiz");
    await expect(page).toHaveURL(/index.php\?page=view_exercise&id=999/);
    await expect(page.locator("body")).toContainText("Exercice IA de test");
  });
});
