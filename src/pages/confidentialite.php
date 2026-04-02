<?php
$page_title = 'Confidentialité - MonCoachScolaire';
$page_css = 'legal-pages.css';
$page_description = 'Politique de confidentialité et protection des données des élèves et enseignants sur MonCoachScolaire.fr';

// ========== 1. PROTECTION SESSION & CONFIG ==========
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}

// Mode démo accessible sans login
$is_demo = !isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description ?? '') ?>">
    <link rel="stylesheet" href="<?= asset_url("assets/css/pages/{$page_css}") ?>">
</head>
<body class="bg-gray-50 text-gray-900">
    <?php
    // Inclure la topbar standard du projet
    if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
        require_once dirname(__DIR__, 2) . '/includes/topbar.php';
    }
?>
    <?php
// Inclure le footer standard du projet
if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
    require_once dirname(__DIR__, 2) . '/includes/footer.php';
}
?>

    <main class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-2xl p-8 md:p-12 mb-8 border border-gray-100">
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 to-indigo-700 bg-clip-text text-transparent mb-6">
                    📜 Confidentialité
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Vos données sont protégées. Découvrez comment nous protégeons la vie privée des élèves, parents et enseignants.
                </p>
            </div>

            <div class="space-y-8">
                <!-- 1. Engagement RGPD -->
                <section class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8 border-l-4 border-blue-500">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-semibold mr-3">1</span>
                        Conformité RGPD
                    </h2>
                    <div class="prose prose-lg max-w-none text-gray-700">
                        <p>MonCoachScolaire respecte intégralement le <strong>Règlement Général sur la Protection des Données (RGPD)</strong> et la loi Informatique & Libertés.</p>
                        <ul class="mt-4 space-y-2">
                            <li>✅ Hébergeur en France (Hostinger EU)</li>
                            <li>✅ Serveur sécurisé HTTPS/TLS 1.3</li>
                            <li>✅ Données jamais vendues/partagées</li>
                            <li>✅ Délai de suppression : 1 an max</li>
                        </ul>
                    </div>
                </section>

                <!-- 2. Données collectées -->
                <section class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-8 border-l-4 border-emerald-500">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center text-white font-semibold mr-3">2</span>
                        Données collectées
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 bg-white rounded-lg shadow">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Données</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Finalité</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">Durée</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Identité</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Nom, Prénom, Niveau scolaire</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Accès personnalisé aux exercices</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Supprimé à la demande</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Technique</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">IP anonymisée, User-Agent</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Sécurité & statistiques anonymes</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">30 jours</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Usage</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Exercices résolus, scores</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Suivi pédagogique anonymisé</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">1 an scolaire</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 3. Sécurité -->
                <section class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-8 border-l-4 border-orange-500">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center text-white font-semibold mr-3">3</span>
                        Mesures de sécurité
                    </h2>
                    <div class="grid md:grid-cols-2 gap-6 mt-6">
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3 mt-0.5">🔒</span>
                                <span class="text-gray-700">Chiffrement HTTPS complet</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3 mt-0.5">🛡️</span>
                                <span class="text-gray-700">Protection CSRF sur tous les forms</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3 mt-0.5">⚡</span>
                                <span class="text-gray-700">Sessions sécurisées PDO préparé</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3 mt-0.5">📱</span>
                                <span class="text-gray-700">Responsive & accessible WCAG 2.1</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. Droits utilisateurs -->
                <section class="bg-gradient-to-r from-purple-50 to-violet-50 rounded-xl p-8 border-l-4 border-purple-500">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center text-white font-semibold mr-3">4</span>
                        Vos droits RGPD
                    </h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-semibold text-lg mb-4">🚀 Actions immédiates</h3>
                            <ul class="space-y-2 text-gray-700">
                                <li><strong>📥 Accès</strong> : Demandez vos données exportées</li>
                                <li><strong>✏️ Rectification</strong> : Corrigez vos informations</li>
                                <li><strong>🗑️ Suppression</strong> : Effacez votre compte</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-4">📧 Contact DPO</h3>
                            <div class="bg-white p-6 rounded-xl shadow-sm border">
                                <p class="text-sm text-gray-600 mb-2">Délégué Protection Données</p>
                                <p class="font-semibold text-lg">dpo@moncoachscolaire.fr</p>
                                <p class="text-xs text-gray-500 mt-2">Réponse sous 72h ouvrées</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. Cookies -->
                <section class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-xl p-8 border-l-4 border-rose-500">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">🍪 Cookies</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold mb-3">Strictement nécessaires</h3>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <li>PHPSESSID (session utilisateur)</li>
                                <li>csrf_token (sécurité forms)</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-3">Aucun cookie tiers</h3>
                            <p class="text-sm text-gray-600">Pas de Google Analytics, Facebook Pixel ou traqueurs.</p>
                        </div>
                    </div>
                </section>
            </div>

            <div class="mt-16 pt-12 border-t border-gray-200">
                <p class="text-center text-sm text-gray-500">
                    Dernière mise à jour : <strong><?= date('d/m/Y') ?></strong> |
                    <a href="mailto:dpo@moncoachscolaire.fr" class="text-blue-600 hover:text-blue-800 font-medium">Nous contacter</a>
                </p>
            </div>
        </div>
    </main>



    <!-- Scripts -->
    <script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
