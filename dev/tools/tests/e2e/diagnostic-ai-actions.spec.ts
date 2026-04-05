import { expect, test } from "@playwright/test";

const diagnosticListPayload = {
  data: [
    {
      id: 9991,
      title: "Diagnostic Anglais - Temps simples",
      subject: "Anglais",
      level: "4eme",
      description: "Quiz de test pour le smoke AI.",
    },
  ],
  total_pool: 1,
  recommendation: { id: 9991 },
};

const quizPayload = {
  quiz: {
    questions: [
      {
        question: "Choisis la bonne forme : I ___ to school yesterday.",
        type: "qcm",
        choices: ["go", "went", "gone"],
      },
      {
        question: "Complète : She ___ happy today.",
        type: "texte",
      },
    ],
  },
};

const submitPayload = {
  success: true,
  data: {
    quiz_id: 9991,
    quiz_title: "Diagnostic Anglais - Temps simples",
    level: "4eme",
    subject: "Anglais",
    score: 35,
    passed: false,
    correct_count: 1,
    total_questions: 2,
    passing_score: 70,
    xp_gained: 7,
    xp_total: 42,
    anti_farming: true,
    results: [
      {
        question_id: 1,
        question: "Choisis la bonne forme : I ___ to school yesterday.",
        type: "qcm",
        user_answer: "go",
        expected_answer: "went",
        is_correct: false,
        correction: "Avec yesterday, on attend le prétérit : went.",
      },
      {
        question_id: 2,
        question: "Complète : She ___ happy today.",
        type: "texte",
        user_answer: "is",
        expected_answer: "is",
        is_correct: true,
        correction: "Le verbe be au présent avec she donne is.",
      },
    ],
    feedback: {
      message: "Tu as une notion clé à retravailler avant le prochain essai.",
      strengths: ["Q2"],
      to_review: ["Q1"],
    },
  },
};

const reviewSubmitPayload = {
  success: true,
  data: {
    quiz_id: 9991,
    quiz_title: "Diagnostic Anglais - Temps simples",
    level: "4eme",
    subject: "Anglais",
    score: 100,
    old_score: 35,
    is_review: true,
    passed: true,
    correct_count: 1,
    total_questions: 1,
    passing_score: 70,
    xp_gained: 9,
    xp_total: 51,
    anti_farming: true,
    results: [
      {
        question_id: 1,
        question: "Choisis la bonne forme : I ___ to school yesterday.",
        type: "qcm",
        user_answer: "went",
        expected_answer: "went",
        is_correct: true,
        correction: "Avec yesterday, on attend le prétérit : went.",
      },
    ],
    feedback: {
      message: "Belle progression : tu as corrigé l'erreur ciblée.",
      strengths: ["Q1"],
      to_review: [],
    },
  },
};

const explanationPayload = {
  success: true,
  data: {
    summary: "Tu confonds ici le temps attendu par l'indice temporel.",
    learning_objective: "Repérer l'indice qui impose le prétérit.",
    mistake_pattern:
      "Tu es resté au présent alors que la phrase parle du passé.",
    steps: [
      "Repère l'indice temporel dans la phrase.",
      "Choisis le temps verbal cohérent.",
      "Vérifie la forme du verbe irrégulier.",
    ],
    retry_tip: "Cherche d'abord le mot qui indique le temps.",
    verification_question: "Quel mot te dit ici que l'action est passée ?",
    per_question: [
      {
        question: "Choisis la bonne forme : I ___ to school yesterday.",
        your_answer: "go",
        correct_answer: "went",
        explanation: "Yesterday impose le prétérit. Le verbe go devient went.",
      },
    ],
  },
};

const preciseCoursePayload = {
  success: true,
  data: {
    title: "Mini-cours ciblé - Prétérit anglais",
    summary: "On revoit quand utiliser le prétérit en anglais.",
    concept_focus: "Un indice temporel passé impose souvent le prétérit.",
    key_points: [
      "Yesterday indique une action passée.",
      "Go devient went au prétérit.",
    ],
    method_steps: [
      "Repère le marqueur de temps.",
      "Choisis le bon temps verbal.",
      "Vérifie la forme irrégulière.",
    ],
    worked_example:
      "I went to school yesterday : l'action est passée et go devient went.",
    common_pitfalls: ["Garder go au lieu de went."],
    practice_tip:
      "Réécris trois phrases avec yesterday et change le verbe au prétérit.",
    verification_question: "Comment écris-tu go au prétérit ?",
    references: [
      {
        title: "Fiche de conjugaison anglaise",
        url: "https://example.test/preterit",
        source: "MonCoachScolaire",
      },
    ],
  },
};

async function stubDiagnosticAiFlow(page) {
  await page.route("**/src/api/diagnostic.php?**", async (route) => {
    await route.fulfill({ json: diagnosticListPayload });
  });

  await page.route("**/src/api/quiz.php?id=9991**", async (route) => {
    await route.fulfill({ json: quizPayload });
  });

  await page.route("**/src/api/diagnostic/submit.php", async (route) => {
    await route.fulfill({ json: submitPayload });
  });

  await page.route(
    "**/index.php?page=api/ia/generate_exercise_explanation**",
    async (route) => {
      await route.fulfill({ json: explanationPayload });
    },
  );

  await page.route(
    "**/index.php?page=api/ia/generate_precise_course**",
    async (route) => {
      await route.fulfill({ json: preciseCoursePayload });
    },
  );
}

async function stubDiagnosticAiFlowWithReview(page) {
  let submitCount = 0;

  await page.route("**/src/api/diagnostic.php?**", async (route) => {
    await route.fulfill({ json: diagnosticListPayload });
  });

  await page.route("**/src/api/quiz.php?id=9991**", async (route) => {
    await route.fulfill({ json: quizPayload });
  });

  await page.route("**/src/api/diagnostic/submit.php", async (route) => {
    submitCount += 1;
    await route.fulfill({
      json: submitCount === 1 ? submitPayload : reviewSubmitPayload,
    });
  });

  await page.route(
    "**/index.php?page=api/ia/generate_exercise_explanation**",
    async (route) => {
      await route.fulfill({ json: explanationPayload });
    },
  );

  await page.route(
    "**/index.php?page=api/ia/generate_precise_course**",
    async (route) => {
      await route.fulfill({ json: preciseCoursePayload });
    },
  );
}

async function reachDiagnosticResult(page) {
  await page.goto("?page=diagnostic&level=4eme&subject=Anglais");

  const firstCard = page.locator("button.quiz-card").first();
  await expect(firstCard).toBeVisible({ timeout: 15000 });
  await firstCard.click();

  await expect(page.locator(".question").first()).toBeVisible({
    timeout: 15000,
  });

  await page.locator('input[type="radio"]').first().check({ force: true });
  await page.locator('input[name="q1"]').fill("is");
  await page.locator('button:has-text("Corriger mon diagnostic")').click();
}

test.describe("Diagnostic AI actions", () => {
  test("ouvre l'explication et le mini-cours depuis le résultat diagnostic", async ({
    page,
  }) => {
    await stubDiagnosticAiFlow(page);
    await reachDiagnosticResult(page);

    const explanationButton = page.locator(
      '[data-ai-action="diagnostic-explanation"]',
    );
    const courseButton = page.locator(
      '[data-ai-action="diagnostic-precise-course"]',
    );

    await expect(explanationButton).toBeVisible({ timeout: 15000 });
    await expect(courseButton).toBeVisible({ timeout: 15000 });

    await explanationButton.click();
    await expect(page.locator("#course-modal")).toHaveClass(/active/);
    await expect(page.locator("#course-title")).toContainText(
      "Comprendre mes erreurs",
    );
    await expect(page.locator("#course-body")).toContainText(
      "Yesterday impose le prétérit",
    );

    await page.locator(".modal-close").click();
    await expect(page.locator("#course-modal")).not.toHaveClass(/active/);

    await courseButton.click();
    await expect(page.locator("#course-modal")).toHaveClass(/active/);
    await expect(page.locator("#course-title")).toContainText(
      "Mini-cours ciblé - Prétérit anglais",
    );
    await expect(page.locator("#course-body")).toContainText(
      "Go devient went au prétérit",
    );
    await expect(page.locator("#course-body")).toContainText(
      "Fiche de conjugaison anglaise",
    );
  });

  test("ouvre les corrections guidées puis réutilise les deux actions IA", async ({
    page,
  }) => {
    await stubDiagnosticAiFlow(page);
    await reachDiagnosticResult(page);

    const correctionsButton = page.locator(
      'button:has-text("Voir les corrections pour progresser")',
    );
    await expect(correctionsButton).toBeVisible({ timeout: 15000 });
    await correctionsButton.click();

    await expect(page.locator("text=Corrections guidees")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("text=Réponse attendue")).toBeVisible({
      timeout: 15000,
    });

    const explanationButton = page.locator(
      '[data-ai-action="diagnostic-explanation"]',
    );
    const courseButton = page.locator(
      '[data-ai-action="diagnostic-precise-course"]',
    );

    await expect(explanationButton).toBeVisible({ timeout: 15000 });
    await expect(courseButton).toBeVisible({ timeout: 15000 });

    await explanationButton.click();
    await expect(page.locator("#course-modal")).toHaveClass(/active/);
    await expect(page.locator("#course-body")).toContainText(
      "Yesterday impose le prétérit",
    );

    await page.locator(".modal-close").click();
    await expect(page.locator("#course-modal")).not.toHaveClass(/active/);

    await courseButton.click();
    await expect(page.locator("#course-modal")).toHaveClass(/active/);
    await expect(page.locator("#course-title")).toContainText(
      "Mini-cours ciblé - Prétérit anglais",
    );
    await expect(page.locator("#course-body")).toContainText(
      "Go devient went au prétérit",
    );
  });

  test("ouvre la révision ciblée puis affiche un résultat de révision", async ({
    page,
  }) => {
    await stubDiagnosticAiFlowWithReview(page);
    await reachDiagnosticResult(page);

    const reviewButton = page.locator(
      'button:has-text("Revoir les questions ratées")',
    );
    await expect(reviewButton).toBeVisible({ timeout: 15000 });
    await reviewButton.click();

    await expect(page.locator("text=Révision ciblée")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("text=Questions à revoir : Q1")).toBeVisible({
      timeout: 15000,
    });

    await page.locator('input[type="radio"]').nth(1).check({ force: true });
    await page.locator('button:has-text("Corriger ma révision")').click();

    await expect(page.locator("text=Score initial : 35/100")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("text=+65.0 points")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("text=Belle progression")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("text=Q1")).toBeVisible();
  });
});
