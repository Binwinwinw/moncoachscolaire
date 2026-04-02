<?php
$levels = ['6eme', '5eme', '4eme', '3eme', 'college', 'lycee'];
$current_level = $_GET['level'] ?? $_GET['niveau'] ?? '6eme';
?>

<nav class="level-tabs mb-12 flex flex-wrap justify-center gap-4 lg:gap-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-3xl shadow-xl">
    <?php foreach ($levels as $level):
        $is_active = $level === $current_level;
        $page_name = match ($level) {
            'college' => 'exercices-college',
            'lycee' => 'exercices-lycee',
            default => "exercices-{$level}",
        };
        ?>
    <a href="?page=<?= $page_name ?>&level=<?= $level ?>"
       class="level-tab px-8 py-4 rounded-2xl font-semibold text-lg shadow-md transition-all duration-300 <?=
               $is_active
               ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-2xl scale-105'
               : 'bg-white text-gray-700 hover:bg-blue-50 hover:text-blue-700 hover:shadow-xl hover:scale-105'
        ?>">
        <?= strtoupper($level) ?>
    </a>
    <?php endforeach; ?>
</nav>

<div id="exercises-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
    <!-- Exercices chargés via PHP/JS ici -->
</div>
