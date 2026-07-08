/**
 * Modales matières — guides de remédiation (tous niveaux).
 */
(function () {
    'use strict';

    function openMatiereModal(nom) {
        var modal = document.getElementById('modal-' + nom);
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        var focusable = modal.querySelectorAll(
            'a, button, textarea, input, select, [tabindex]:not([tabindex="-1"])'
        );
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        modal._prevActive = document.activeElement;

        setTimeout(function () {
            if (first) {
                first.focus();
            }
        }, 100);

        function trap(e) {
            if (e.key !== 'Tab' || focusable.length === 0) {
                return;
            }
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }

        modal._trap = trap;
        modal.addEventListener('keydown', trap);

        function escListener(e) {
            if (e.key === 'Escape') {
                closeMatiereModal(nom);
            }
        }
        modal._escListener = escListener;
        document.addEventListener('keydown', escListener);

        modal._bgListener = function (e) {
            if (e.target === modal) {
                closeMatiereModal(nom);
            }
        };
        modal.addEventListener('click', modal._bgListener);
    }

    function closeMatiereModal(nom) {
        var modal = document.getElementById('modal-' + nom);
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        if (modal._trap) {
            modal.removeEventListener('keydown', modal._trap);
        }
        if (modal._escListener) {
            document.removeEventListener('keydown', modal._escListener);
        }
        if (modal._bgListener) {
            modal.removeEventListener('click', modal._bgListener);
        }
        if (modal._prevActive && typeof modal._prevActive.focus === 'function') {
            setTimeout(function () {
                modal._prevActive.focus();
            }, 100);
        }
    }

    window.openMatiereModal = openMatiereModal;
    window.closeMatiereModal = closeMatiereModal;
    window.openTermModal = openMatiereModal;
    window.closeTermModal = closeMatiereModal;
})();
