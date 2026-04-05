import { expect, test } from "@playwright/test";

async function login(page, username: string, password: string) {
  await page.goto("?page=login");
  await page.fill("#username", username);
  await page.fill("#password", password);
  await page.click("#submit-btn");
}

test.describe("Rattachement parent/enfant", () => {
  test("un parent génère un code, un élève le rattache, puis le détail enfant reste accessible", async ({
    page,
  }) => {
    await login(page, "adminparent1", "admin123");

    await page.waitForURL(
      /page=parents%2Fdashboard_parent|page=parents\/dashboard_parent|dashboard_parent/,
      {
        timeout: 20000,
      },
    );

    const generateButton = page.locator("#generate-family-code");
    await expect(generateButton).toBeVisible({ timeout: 15000 });
    await generateButton.click();

    const codeOutput = page.locator("#family-code-output");
    await expect(codeOutput).toContainText("Code :", { timeout: 15000 });

    const inviteCode = (await codeOutput.textContent())
      ?.replace("Code :", "")
      .trim();

    expect(inviteCode).toBeTruthy();
    expect(inviteCode).toMatch(/^[A-Z0-9]{6}$/);

    await page.goto("?page=logout");

    await login(page, "demo", "demo");

    await expect(page.locator('a[href*="page=logout"]')).toBeVisible({
      timeout: 15000,
    });

    const attachResult = await page.evaluate(async (code) => {
      const response = await fetch("?page=api/parent_family", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": window.csrfToken || "",
        },
        body: JSON.stringify({
          action: "attach_code",
          code,
          csrf_token: window.csrfToken || "",
        }),
        credentials: "same-origin",
      });

      const data = await response.json();

      const familyResponse = await fetch(
        "?page=api/parent_family&action=my_family",
        {
          credentials: "same-origin",
        },
      );
      const familyData = await familyResponse.json();

      return {
        responseOk: response.ok,
        attach: data,
        familyOk: familyResponse.ok,
        family: familyData,
      };
    }, inviteCode);

    expect(attachResult.responseOk).toBeTruthy();
    expect(attachResult.attach?.success).toBeTruthy();
    expect(attachResult.attach?.data?.message).toMatch(/Parent rattaché/i);
    expect(attachResult.familyOk).toBeTruthy();
    expect(Array.isArray(attachResult.family?.data?.parents)).toBeTruthy();
    expect(attachResult.family.data.parents.length).toBeGreaterThan(0);

    await page.goto("?page=logout");

    await login(page, "adminparent1", "admin123");

    await page.waitForURL(
      /page=parents%2Fdashboard_parent|page=parents\/dashboard_parent|dashboard_parent/,
      {
        timeout: 20000,
      },
    );

    const detailLinks = page.getByRole("link", { name: "Voir le détail" });
    await expect(detailLinks.first()).toBeVisible({ timeout: 15000 });

    const detailHref = await detailLinks.first().getAttribute("href");
    expect(detailHref).toMatch(/parents(%2F|\/)?suivi_enfant.*id=\d+/i);

    await detailLinks.first().click();

    await page.waitForURL(
      /page=parents%2Fsuivi_enfant|page=parents\/suivi_enfant|suivi_enfant/,
      {
        timeout: 20000,
      },
    );

    await expect(page.locator("text=Suivi").first()).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("text=Niveau :").first()).toBeVisible({
      timeout: 15000,
    });
  });
});
