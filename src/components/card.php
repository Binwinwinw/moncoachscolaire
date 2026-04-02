<?php
// src/components/card.php
// Usage: set $card_id (optional), $card_title, $card_image_src (optional), $card_image_alt, $card_content_html, $card_cta_text, $card_cta_url, $card_variant ("mascotte"|"special"|"default"), $card_extra_classes

$card_id = $card_id ?? ('mcs-card-' . bin2hex(random_bytes(4)));
$card_variant = $card_variant ?? 'default';
$card_classes = 'mcs-card mcs-card-bg mcs-card--' . $card_variant . ' flex flex-col items-center rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 hover:scale-105 ' . ($card_extra_classes ?? '');
?>
<article id="<?php echo htmlspecialchars($card_id); ?>" class="<?php echo htmlspecialchars($card_classes); ?>" role="group" aria-labelledby="<?php echo htmlspecialchars($card_id); ?>-title">
  <?php if (!empty($card_image_src)): ?>
    <div class="mcs-card-media mb-4">
      <img src="<?php echo htmlspecialchars($card_image_src); ?>" alt="<?php echo htmlspecialchars($card_image_alt ?? ''); ?>" width="128" height="128" loading="lazy" class="rounded-full w-32 h-32 object-cover shadow-md">
    </div>
  <?php endif; ?>

  <div class="mcs-card-body text-center flex-1 flex flex-col justify-between w-full">
    <?php if (!empty($card_title)): ?>
      <h3 id="<?php echo htmlspecialchars($card_id); ?>-title" class="text-xl font-bold mb-2"><?php echo $card_title; ?></h3>
    <?php endif; ?>

    <div class="mcs-card-content text-base text-slate-700 mb-4">
      <?php echo $card_content_html ?? ''; ?>
    </div>
    <?php if (!empty($card_cta_text) && !empty($card_cta_url)): ?>
      <a class="mcs-card-cta inline-block mt-auto px-6 py-2 rounded-full font-bold shadow transition-colors duration-200
        <?php if ($card_variant === 'special') {
            echo 'bg-yellow-400 text-white hover:bg-yellow-500';
        } elseif ($card_variant === 'mascotte') {
            echo 'bg-blue-600 text-white hover:bg-blue-700';
        } else {
            echo 'bg-slate-200 text-slate-800 hover:bg-slate-300';
        } ?>"
        href="<?php echo htmlspecialchars($card_cta_url); ?>"><?php echo htmlspecialchars($card_cta_text); ?></a>
    <?php endif; ?>
  </div>
<?php
/**
 * Component Card - MonCoachScolaire
 *
 * @param string $card_id ID unique (auto-généré si absent)
 * @param string $card_title Titre de la carte
 * @param string $card_image_src URL de l'image (optionnel)
 * @param string $card_image_alt Texte alternatif de l'image
 * @param string $card_content_html Contenu HTML (description, liste, etc.)
 * @param string $card_cta_text Texte du bouton CTA
 * @param string $card_cta_url URL du bouton CTA
 * @param string $card_variant Style : "mascotte" | "special" | "default"
 * @param string $card_extra_classes Classes CSS supplémentaires
 * @param string $card_image_size Taille image : "small" (128px) | "medium" (160px) | "large" (192px)
 * @param string $card_border_color Couleur de bordure Tailwind (ex: "blue-500")
 */

// Valeurs par défaut
$card_id = $card_id ?? ('mcs-card-' . bin2hex(random_bytes(4)));
$card_variant = $card_variant ?? 'default';
$card_image_size = $card_image_size ?? 'medium';
$card_border_color = $card_border_color ?? 'blue-400';
$card_extra_classes = $card_extra_classes ?? '';

// Mapper les tailles d'image
$image_size_map = [
    'small' => 'w-32 h-32',     // 128px
    'medium' => 'w-40 h-40',    // 160px (NOUVEAU DÉFAUT)
    'large' => 'w-48 h-48',     // 192px
    'xlarge' => 'w-56 h-56',     // 224px
];
$image_size_class = $image_size_map[$card_image_size] ?? $image_size_map['medium'];

// Classes de la carte
$card_classes = 'mcs-card mcs-card--' . $card_variant . ' flex flex-col items-center rounded-xl shadow-lg bg-white p-6 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 hover:scale-105 ' . $card_extra_classes;

// Classes du CTA selon la variante
$cta_classes_map = [
    'special' => 'bg-gradient-to-r from-yellow-400 to-yellow-500 text-white hover:from-yellow-500 hover:to-yellow-600',
    'mascotte' => 'bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800',
    'default' => 'bg-slate-200 text-slate-800 hover:bg-slate-300',
];
$cta_classes = $cta_classes_map[$card_variant] ?? $cta_classes_map['default'];
?>

<article
    id="<?php echo htmlspecialchars($card_id); ?>"
    class="<?php echo htmlspecialchars($card_classes); ?>"
    role="group"
    aria-labelledby="<?php echo htmlspecialchars($card_id); ?>-title"
>

  <?php if (!empty($card_image_src)): ?>
    <div class="mcs-card-media mb-6">
      <picture>
        <?php
        // Auto-détection du format d'image
        $image_ext = pathinfo($card_image_src, PATHINFO_EXTENSION);
      if (in_array($image_ext, ['avif', 'webp'])) {
          echo '<source srcset="' . htmlspecialchars($card_image_src) . '" type="image/' . $image_ext . '">';
      }
      ?>
        <img
            src="<?php echo htmlspecialchars($card_image_src); ?>"
            alt="<?php echo htmlspecialchars($card_image_alt ?? ''); ?>"
            width="160"
            height="160"
            loading="lazy"
            class="rounded-full <?php echo $image_size_class; ?> object-cover object-center shadow-lg border-4 border-<?php echo htmlspecialchars($card_border_color); ?> hover:scale-110 transition-transform duration-300"
        >
      </picture>
    </div>
  <?php endif; ?>

  <div class="mcs-card-body text-center flex-1 flex flex-col justify-between w-full">

    <?php if (!empty($card_title)): ?>
      <h3 id="<?php echo htmlspecialchars($card_id); ?>-title" class="text-2xl font-bold mb-3 text-slate-800">
        <?php echo $card_title; ?>
      </h3>
    <?php endif; ?>

    <div class="mcs-card-content text-base text-slate-700 mb-6 flex-1">
      <?php echo $card_content_html ?? ''; ?>
    </div>

    <?php if (!empty($card_cta_text) && !empty($card_cta_url)): ?>
      <a
          class="mcs-card-cta inline-flex items-center justify-center gap-2 mt-auto px-6 py-3 rounded-full font-bold shadow-md transition-all duration-200 <?php echo $cta_classes; ?>"
          href="<?php echo htmlspecialchars($card_cta_url); ?>"
      >
        <?php echo htmlspecialchars($card_cta_text); ?>
      </a>
    <?php endif; ?>

  </div>

</article>
