import { test, expect } from "@playwright/test";

/**
 * Test E2E : Exercice interactif → Réponse incorrecte → (Voir mini-cours) → Modal affichée
 *
 * Flux complet:
 * 1. Charger une page d'exercices (6ème Mathématiques)
 * 2. Attendre un exercice QCM
 * 3. Répondre incorrectement
 * 4. Vérifier le bouton "📘 Voir le mini-cours ciblé" apparaît
 * 5. Cliquer sur le bouton
 * 6. Vérifier que le modal s'ouvre avec du contenu Groq
 * 7. Cliquer sur "J'ai compris ! 💪" pour fermer
 */

test.describe("Mini-cours interactif: Exercice → Erreur → Modal", () => {
  test.beforeEach(async ({ page }) => {
    // Mock Groq API pour obtenir une réponse déterministe
    await page.route(
      "**/index.php?page=api/ia/generate_precise_course",
      async (route) => {
        const body = await route.request.postDataJSON();

        // Groq réponse mockée
        const mockedResponse = {
          success: true,
          data: {
            title: `Mini-cours ciblé - ${body.subject || "La notion"}`,
            summary:
              "On reprend la notion qui bloque pour réussir le prochain essai.",
            concept_focus: `Comprendre la méthode pour "${body.incorrect_items?.[0]?.question || "la question"}"`,
            key_points: [
              "Lecture attentive de la consigne",
              "Repérage de l'indice central",
              "Application de la méthode",
            ],
            method_steps: [
              "Lis la consigne jusqu'au bout",
              "Identifie ce qui est vraiment demandé",
              "Applique la méthode pas à pas",
            ],
            worked_example: `Pour la question "${body.incorrect_items?.[0]?.question || "la question"}", la bonne réponse est "${body.incorrect_items?.[0]?.correct_answer || "la bonne réponse"}" car elle respecte la méthode attendue.`,
            common_pitfalls: [
              "Répondre trop vite sans vérifier",
              "Confondre deux concepts proches",
              "Oublier une étape clé",
            ],
            practice_tip:
              "Refais un exercice du même type en expliquant à voix haute chaque étape.",
            verification_question:
              "Peux-tu refaire cet exercice en verbalisant chaque étape ?",
            references: [],
          },
          provider_used: "groq",
        };

        await route.fulfill({
          status: 200,
          contentType: "application/json",
          body: JSON.stringify(mockedResponse),
        });
      },
    );
  });

  test("Affiche le bouton 'Voir mini-cours' après une réponse incorrecte", async ({
    page,
  }) => {
    // 1. Naviguer vers la page d'exercices 6ème
    await page.goto("/index.php?page=eleve/college/6eme/exercices-6eme");

    // 2. Attendre que un exercice QCM se charge
    const exerciseCard = page.locator(".exercise-card").first();
    await expect(exerciseCard).toBeVisible({ timeout: 10000 });

    // 3. Trouver le premiers bouton "Répondre" / submit du QCM
    const submitBtn = exerciseCard
      .locator("button:has-text('Vérifier')")
      .first();
    if (!submitBtn) {
      test.skip();
      return; // Skip si pas de QCM trouvé
    }

    // 4. Sélectionner une mauvaise réponse (première option qui ne l'est probablement pas)
    const options = exerciseCard.locator("input[type='radio']");
    const optionCount = await options.count();
    if (optionCount > 0) {
      await options.first().check();
    }

    // 5. Soumettre la réponse
    await submitBtn.click();

    // 6. Attendre la réaction (feedback)
    await page.waitForTimeout(1000);

    // 7. Vérifier que le bouton "📘 Voir le mini-cours ciblé" est visible
    const courseButton = exerciseCard.locator("button:has-text('mini-cours')");
    await expect(courseButton).toBeVisible({ timeout: 5000 });
  });

  test("Ouvre le modal quand on clique sur 'Voir le mini-cours ciblé'", async ({
    page,
  }) => {
    // Reprendre l'état de l'exercice erreur
    await page.goto("/index.php?page=eleve/college/6eme/exercices-6eme");

    const exerciseCard = page.locator(".exercise-card").first();
    await expect(exerciseCard).toBeVisible({ timeout: 10000 });

    const submitBtn = exerciseCard
      .locator("button:has-text('Vérifier')")
      .first();
    if (!submitBtn) {
      test.skip();
      return;
    }

    // Répondre incorrectement
    const options = exerciseCard.locator("input[type='radio']");
    if ((await options.count()) > 0) {
      await options.first().check();
    }
    await submitBtn.click();
    await page.waitForTimeout(1000);

    // Cliquer sur le bouton "mini-cours"
    const courseButton = exerciseCard.locator("button:has-text('mini-cours')");
    await courseButton.click();

    // Attendre et vérifier le modal
    const modal = page.locator("#course-modal");
    await expect(modal).toHaveClass(/active/);

    // Vérifier que le titre du modal contient du contenu
    const title = page.locator("#course-title");
    await expect(title).toContainText("Mini-cours");

    // Vérifier que le corps du modal a du contenu (attendu depuis la mock)
    const body = page.locator("#course-body");
    await expect(body).toContainText(/Points clés|Étapes|Exemple/i);
  });

  test("Ferme le modal avec le bouton 'J'ai compris ! 💪'", async ({
    page,
  }) => {
    await page.goto("/index.php?page=eleve/college/6eme/exercices-6eme");

    const exerciseCard = page.locator(".exercise-card").first();
    await expect(exerciseCard).toBeVisible({ timeout: 10000 });

    // Erreur + ouverture du modal
    const submitBtn = exerciseCard
      .locator("button:has-text('Vérifier')")
      .first();
    if (!submitBtn) {
      test.skip();
      return;
    }

    const options = exerciseCard.locator("input[type='radio']");
    if ((await options.count()) > 0) {
      await options.first().check();
    }
    await submitBtn.click();
    await page.waitForTimeout(1000);

    const courseButton = exerciseCard.locator("button:has-text('mini-cours')");
    await courseButton.click();

    // Vérifier que le modal est visibleaactif
    const modal = page.locator("#course-modal");
    await expect(modal).toHaveClass(/active/);

    // Cliquer sur le bouton de fermeture "J'ai compris ! 💪"
    const closeBtn = modal.locator("button:has-text('J'ai compris')");
    await closeBtn.click();

    // Vérifier que le modal n'est plus visible
    await expect(modal).not.toHaveClass(/active/);
  });

  test("Peut aussi accéder au mini-cours sur d'autres niveaux (5ème, 3ème, etc.)", async ({
    page,
  }) => {
    // Tester la 3ème pour variété
    await page.goto(
      "http://127.0.0.1:8081/index.php?page=eleve/college/3eme/exercices-3eme",
    );

    const exerciseCard = page.locator(".exercise-card").first();
    await expect(exerciseCard).toBeVisible({ timeout: 10000 });

    const submitBtn = exerciseCard
      .locator("button:has-text('Vérifier')")
      .first();
    if (!submitBtn) {
      test.skip();
      return;
    }

    const options = exerciseCard.locator("input[type='radio']");
    if ((await options.count()) > 0) {
      await options.first().check();
    }
    await submitBtn.click();
    await page.waitForTimeout(1000);

    // Vérifier le bouton est présent
    const courseButton = exerciseCard.locator("button:has-text('mini-cours')");
    await expect(courseButton).toBeVisible({ timeout: 5000 });
  });
});
