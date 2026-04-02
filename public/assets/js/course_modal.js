// JS pour gérer l'ouverture/fermeture de la modale d'exercice et l'injection du contenu
function openCourseModal(html, title = "Exercice") {
    const modal = document.getElementById("course-modal");
    const body = document.getElementById("course-body");
    const header = document.getElementById("course-title");
    if (!modal || !body) return;
    body.innerHTML =
        html ||
        '<div class="text-red-600">Erreur de chargement de l\'exercice.</div>';
    if (header && title) header.textContent = title;
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeCourseModal() {
    const modal = document.getElementById("course-modal");
    if (modal) modal.classList.remove("active");
    document.body.style.overflow = "";
}

// Pour compatibilité inline
window.openCourseModal = openCourseModal;
window.closeCourseModal = closeCourseModal;
