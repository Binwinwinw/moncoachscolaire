/**
 * sidebar.js - Gestion de l'interactivité de la sidebar
 * Fonctionnalités : toggle, animations, gestion de l'état
 */

(function() {
    'use strict';

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        initSidebarToggle();
        initSidebarSubmenus();
    });

    /**
     * Initialise le bouton de toggle de la sidebar dans le header
     */
    function initSidebarToggle() {
        const toggleButton = document.getElementById('headerSidebarToggle');
        const sidebar = document.getElementById('site-sidebar');
        const body = document.body;

        if (!toggleButton || !sidebar) {
            return; // Sidebar ou bouton non présents
        }

        toggleButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const isHidden = body.classList.contains('sidebar-hidden');
            
            if (isHidden) {
                // Afficher la sidebar
                body.classList.remove('sidebar-hidden');
                sidebar.setAttribute('aria-hidden', 'false');
                toggleButton.setAttribute('aria-expanded', 'true');
            } else {
                // Masquer la sidebar
                body.classList.add('sidebar-hidden');
                sidebar.setAttribute('aria-hidden', 'true');
                toggleButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /**
     * Initialise les sous-menus dépliables de la sidebar
     */
    function initSidebarSubmenus() {
        const toggleButtons = document.querySelectorAll('.sidebar-toggle');
        
        toggleButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const group = button.closest('.sidebar-group');
                const submenu = group ? group.querySelector('.sidebar-submenu') : null;
                
                if (!submenu) {
                    return;
                }

                const isExpanded = submenu.getAttribute('data-expanded') === 'true';
                const submenuId = submenu.getAttribute('id');

                if (isExpanded) {
                    // Fermer le sous-menu
                    submenu.setAttribute('aria-hidden', 'true');
                    submenu.setAttribute('data-expanded', 'false');
                    button.setAttribute('aria-expanded', 'false');
                    group.classList.remove('active');
                } else {
                    // Ouvrir le sous-menu
                    submenu.setAttribute('aria-hidden', 'false');
                    submenu.setAttribute('data-expanded', 'true');
                    button.setAttribute('aria-expanded', 'true');
                    group.classList.add('active');
                }
            });
        });
    }

    // Exposer certaines fonctions globalement si nécessaire
    window.Sidebar = {
        toggle: function() {
            const toggleButton = document.getElementById('headerSidebarToggle');
            if (toggleButton) {
                toggleButton.click();
            }
        },
        show: function() {
            const body = document.body;
            const sidebar = document.getElementById('site-sidebar');
            const toggleButton = document.getElementById('headerSidebarToggle');
            
            if (sidebar && toggleButton) {
                body.classList.remove('sidebar-hidden');
                sidebar.setAttribute('aria-hidden', 'false');
                toggleButton.setAttribute('aria-expanded', 'true');
            }
        },
        hide: function() {
            const body = document.body;
            const sidebar = document.getElementById('site-sidebar');
            const toggleButton = document.getElementById('headerSidebarToggle');
            
            if (sidebar && toggleButton) {
                body.classList.add('sidebar-hidden');
                sidebar.setAttribute('aria-hidden', 'true');
                toggleButton.setAttribute('aria-expanded', 'false');
            }
        }
    };

})();

