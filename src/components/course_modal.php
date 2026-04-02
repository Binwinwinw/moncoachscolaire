<?php
// Modal Cours
?>
<div id="course-modal" class="modal">
    <div class="modal-overlay" onclick="closeCourseModal()"></div>
    <div class="modal-content course-modal-content">
        <div class="modal-header">
            <h2 id="course-title">📚 Cours</h2>
            <button class="modal-close" onclick="closeCourseModal()">✕</button>
        </div>

        <div class="modal-body" id="course-body">
            <!-- Contenu du cours chargé dynamiquement -->
        </div>

        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeCourseModal()">
                J'ai compris ! 💪
            </button>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 16px;
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
    color: #1e293b;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #64748b;
    padding: 8px;
    line-height: 1;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #1e293b;
}

.modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

.course-section {
    margin-bottom: 24px;
}

.course-section h3 {
    color: #8b5cf6;
    font-size: 18px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.course-section-content {
    line-height: 1.6;
    color: #475569;
}

.key-points {
    background: #f0fdf4;
    border-left: 4px solid #10b981;
    padding: 16px;
    border-radius: 8px;
    margin: 16px 0;
}

.example-box {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 16px;
    border-radius: 8px;
    margin: 16px 0;
}

.formula-box {
    background: #ede9fe;
    border: 2px solid #8b5cf6;
    padding: 16px;
    border-radius: 8px;
    margin: 16px 0;
    text-align: center;
    font-size: 18px;
    font-weight: 600;
}

.modal-footer {
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: center;
}

.btn {
    padding: 12px 32px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #8b5cf6;
    color: white;
}

.btn-primary:hover {
    background: #7c3aed;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        max-height: 95vh;
    }

    .modal-header, .modal-body, .modal-footer {
        padding: 16px;
    }
}
</style>

<script>
/**
 * Charge et affiche le cours d'un exercice
 * @param {number} exerciseId - ID de l'exercice
 */
async function showCourse(exerciseId) {
    const modal = document.getElementById('course-modal');
    const body = document.getElementById('course-body');

    // Afficher le modal avec loader
    modal.classList.add('active');
    body.innerHTML = '<div class="loading">⏳ Chargement du cours...</div>';

    try {
        const response = await fetch(`/src/api/exercices/get_course.php?exercise_id=${exerciseId}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Erreur de chargement');
        }

        if (!data.data.has_course) {
            body.innerHTML = `
                <div class="no-course">
                    <p>😊 Cet exercice n'a pas encore de cours associé.</p>
                    <p>Essaie de le résoudre par toi-même !</p>
                </div>
            `;
            return;
        }

        const course = data.data.course;

        // Afficher le cours
        let html = '';

        // Section titre
        html += `<div class="course-info">
            <p><strong>📖 Matière :</strong> ${course.subject}</p>
            <p><strong>🎓 Niveau :</strong> ${course.level}</p>
            <p><strong>🎯 Compétence :</strong> ${course.competence}</p>
        </div>`;

        // Points clés
        if (course.key_point) {
            html += `<div class="key-points">
                <h3>💡 Points clés</h3>
                ${course.key_point}
            </div>`;
        }

        // Explication
        if (course.explanation) {
            html += `<div class="course-section">
                <h3>📚 Explication</h3>
                <div class="course-section-content">${course.explanation}</div>
            </div>`;
        }

        // Exemple
        if (course.example) {
            html += `<div class="example-box">
                <h3>📝 Exemple</h3>
                ${course.example}
            </div>`;
        }

        // Formule
        if (course.formula) {
            html += `<div class="formula-box">
                ${course.formula}
            </div>`;
        }

        body.innerHTML = html;

    } catch (error) {
        console.error('Erreur chargement cours:', error);
        body.innerHTML = `
            <div class="error">
                <p>❌ Impossible de charger le cours.</p>
                <p>Réessaie plus tard !</p>
            </div>
        `;
    }
}

/**
 * Ferme le modal du cours
 */
function closeCourseModal() {
    const modal = document.getElementById('course-modal');
    modal.classList.remove('active');
}

// Fermer avec Échap
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeCourseModal();
    }
});
</script>
