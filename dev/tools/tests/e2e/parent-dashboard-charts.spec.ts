import { expect, test } from "@playwright/test";

test.describe("Dashboard parent charts", () => {
  test("le compte parent de test voit des graphiques bornés avec datasets non vides", async ({
    page,
  }) => {
    await page.goto("?page=login");
    await page.fill("#username", "adminparent1");
    await page.fill("#password", "admin123");
    await page.click("#submit-btn");

    await page.waitForURL(
      /page=parents%2Fdashboard_parent|page=parents\/dashboard_parent|dashboard_parent/,
      {
        timeout: 20000,
      },
    );

    await expect(page.locator("text=Statistiques de suivi")).toBeVisible({
      timeout: 15000,
    });
    await expect(page.locator("text=Compte démo")).toHaveCount(0);
    await expect(page.locator("text=Emma (démo)")).toHaveCount(0);
    await expect(page.locator("text=Lucas (démo)")).toHaveCount(0);

    const progressCanvas = page.locator("#chart-parent-progress");
    const successCanvas = page.locator("#chart-parent-success");
    const subjectsCanvas = page.locator("#chart-parent-subjects");

    await expect(progressCanvas).toBeVisible();
    await expect(successCanvas).toBeVisible();
    await expect(subjectsCanvas).toBeVisible();

    const sizes = await page.evaluate(() => {
      const ids = [
        "chart-parent-progress",
        "chart-parent-success",
        "chart-parent-subjects",
      ];

      return ids.map((id) => {
        const canvas = document.getElementById(id);
        if (!(canvas instanceof HTMLCanvasElement)) {
          return { id, found: false };
        }

        const rect = canvas.getBoundingClientRect();
        const parent = canvas.parentElement?.getBoundingClientRect();

        return {
          id,
          found: true,
          width: rect.width,
          height: rect.height,
          parentHeight: parent?.height || 0,
          scrollHeight: canvas.scrollHeight,
        };
      });
    });

    for (const size of sizes) {
      expect(size.found).toBeTruthy();
      expect(size.width).toBeGreaterThan(120);
      expect(size.height).toBeGreaterThan(100);
      expect(size.height).toBeLessThan(320);
      expect(size.parentHeight).toBeGreaterThan(180);
      expect(size.parentHeight).toBeLessThan(280);
      expect(size.scrollHeight).toBeLessThan(340);
    }

    const chartSummary = await page.evaluate(() => {
      const ids = [
        "chart-parent-progress",
        "chart-parent-success",
        "chart-parent-subjects",
      ];

      return ids.map((id) => {
        const canvas = document.getElementById(id);
        const chart =
          canvas && window.Chart && typeof window.Chart.getChart === "function"
            ? window.Chart.getChart(canvas)
            : null;

        return {
          id,
          exists: !!chart,
          labelCount: chart?.data?.labels?.length || 0,
          datasetCount: chart?.data?.datasets?.length || 0,
          firstDatasetLength: chart?.data?.datasets?.[0]?.data?.length || 0,
        };
      });
    });

    for (const chart of chartSummary) {
      expect(chart.exists).toBeTruthy();
      expect(chart.datasetCount).toBeGreaterThan(0);
      expect(chart.labelCount).toBeGreaterThan(0);
      expect(chart.firstDatasetLength).toBeGreaterThan(0);
    }

    const brokenImages = await page.evaluate(() => {
      return Array.from(document.images)
        .filter((image) => !image.complete || image.naturalWidth === 0)
        .map((image) => ({
          src: image.getAttribute("src") || "",
          alt: image.getAttribute("alt") || "",
        }));
    });

    expect(brokenImages).toEqual([]);

    await page.screenshot({
      path: "test-results/parent-dashboard-charts.png",
      fullPage: true,
    });
  });
});
