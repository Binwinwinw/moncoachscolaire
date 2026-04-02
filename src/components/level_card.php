<?php
/**
 * Composant carte "niveau" réutilisable pour accueil Collège, Lycée, Bac
 * Usage :
 *   include src/components/level_card.php
 *   echo render_level_card([...]);
 *
 * Paramètres attendus (array) :
 *   - title (string)
 *   - desc (string)
 *   - features (array)
 *   - ctas (array d'array : [text, url, classes optionnelles])
 *   - variant (string) : 'college' | 'lycee' | 'bac' (pour couleurs)
 *   - extra_classes (string, optionnel)
 *   - special (bool, optionnel)
 */

if (!function_exists('render_level_card')) {
    function render_level_card(array $params)
    {
        $title = $params['title'] ?? '';
        $desc = $params['desc'] ?? '';
        $features = $params['features'] ?? [];
        $ctas = $params['ctas'] ?? [];
        $variant = $params['variant'] ?? 'college';
        $extra_classes = $params['extra_classes'] ?? '';
        $special = !empty($params['special']);

        // Palette par variante
        $palettes = [
            'college' => [
                'bg' => 'bg-green-100',
                'border' => 'border-green-300',
                'title' => 'text-green-800',
                'desc' => 'text-green-700',
                'feature' => 'text-green-700',
            ],
            'lycee' => [
                'bg' => 'bg-violet-100',
                'border' => 'border-violet-300',
                'title' => 'text-violet-800',
                'desc' => 'text-violet-700',
                'feature' => 'text-violet-700',
            ],
            'bac' => [
                'bg' => 'bg-yellow-100',
                'border' => 'border-yellow-300',
                'title' => 'text-yellow-800',
                'desc' => 'text-yellow-700',
                'feature' => 'text-yellow-700',
            ],
        ];
        $p = $palettes[$variant] ?? $palettes['college'];
        $card_classes = "niveau-card p-5 max-w-sm border {$p['border']} {$p['bg']} " . $extra_classes;
        if ($special) {
            $card_classes .= ' border-2 border-[#c9a66b] bg-gradient-to-br from-[#f9f5eb] to-[#f5efe1]';
        }
        ob_start();
        ?>
        <div class="<?php echo htmlspecialchars($card_classes); ?>">
            <h3 class="text-xl font-bold <?php echo $p['title']; ?> mb-2"><?php echo htmlspecialchars($title); ?></h3>
            <p class="text-sm <?php echo $p['desc']; ?> mb-3"><?php echo htmlspecialchars($desc); ?></p>
            <ul class="mb-4 space-y-1 text-sm <?php echo $p['feature']; ?>">
                <?php foreach ($features as $feature): ?>
                    <li class="list-disc list-inside"><?php echo htmlspecialchars($feature); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php foreach ($ctas as $cta): ?>
                <a href="<?php echo htmlspecialchars($cta['url']); ?>"
                   class="inline-flex items-center justify-center px-5 py-2.5 mb-2 rounded-xl <?php echo $cta['classes'] ?? 'bg-[#52796f] text-white text-sm font-semibold hover:bg-[#354f52] transition'; ?>">
                    <?php echo $cta['text']; ?>
                </a><br>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
