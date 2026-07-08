/**
 * Interactions légères pour les cartes cours (hub + cours IA générés).
 */
(function () {
    "use strict";

    function initAccordions(root) {
        root.querySelectorAll("details.cours-accordion").forEach((details) => {
            if (details.dataset.coursInit === "1") return;
            details.dataset.coursInit = "1";
        });
    }

    function initInternalLinks(root) {
        root.querySelectorAll("a[href^='#']").forEach((link) => {
            if (link.dataset.coursLinkInit === "1") return;
            link.dataset.coursLinkInit = "1";
            link.addEventListener("click", function (e) {
                const targetId = link.getAttribute("href").slice(1);
                const target = document.getElementById(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: "smooth", block: "start" });
                }
            });
        });
    }

    function initializeCard(card) {
        initAccordions(card);
        initInternalLinks(card);
    }

    function initAll() {
        document
            .querySelectorAll(".cours-card, .cours-card-hub, .ai-generated-course")
            .forEach(initializeCard);

        const container =
            document.getElementById("random-cours-card") || document.body;
        const obs = new MutationObserver((mutations) => {
            mutations.forEach((m) => {
                m.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) return;
                    if (
                        node.matches &&
                        node.matches(".cours-card, .cours-card-hub, .ai-generated-course")
                    ) {
                        initializeCard(node);
                    } else if (node.querySelectorAll) {
                        node
                            .querySelectorAll(
                                ".cours-card, .cours-card-hub, .ai-generated-course",
                            )
                            .forEach(initializeCard);
                    }
                });
            });
        });
        obs.observe(container, { childList: true, subtree: true });
    }

    window.InteractiveCours = { initAll };

    document.addEventListener("DOMContentLoaded", initAll);
})();
