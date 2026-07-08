<?php
?>
<div class="content-section">
            <h3>🎯 Plan d'action personnalisé</h3>
            <?php if (!$has_access): ?>
                <p>Pour bénéficier d'un accompagnement sur mesure, crée-toi un compte et suis tes progrès !</p>
                <div class="cta-actions flex flex-col sm:flex-row gap-4 justify-center mt-4">
                    <a href="<?php echo site_url('register'); ?>" class="nav-btn nav-register bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400">Créer mon compte 6ème</a>
                    <a href="<?php echo site_url('login'); ?>" class="nav-btn nav-login bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-300">Me connecter</a>
                </div>
            <?php else: ?>
                <p><strong>👋 Salut <?php echo htmlspecialchars($user_name ?? 'Élève'); ?> !</strong> Tu es connecté(e) et peux accéder à tous les contenus personnalisés.</p>
                <div class="cta-actions flex flex-col sm:flex-row gap-4 justify-center mt-4">
                    <a href="<?php echo site_url('college/6eme/exercices-6eme'); ?>" class="nav-btn nav-exercices bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400">📝 Faire des exercices</a>
                    <a href="<?php echo site_url('cours', ['niveau' => '6eme']); ?>" class="nav-btn nav-cours bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400">📚 Voir mes cours</a>
                </div>
            <?php endif; ?>
        </div>
