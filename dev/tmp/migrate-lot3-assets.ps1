$targets = Get-ChildItem src/pages/eleve -Recurse -File -Include *exercices*.php
$replacements = @(
    @{ Old = "(?s)<\?php if \(function_exists\('asset_url'\)\): \?>\s*<link rel=\"stylesheet\" href=\"<\?php echo asset_url\('assets/css/pages/dynamic-exercises\.css'\); \?>\">\s*<\?php else: \?>\s*<link rel=\"stylesheet\" href=\"/assets/css/pages/dynamic-exercises\.css\">\s*<\?php endif; \?>"; New = "<link rel=\"stylesheet\" href=\"<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/dynamic-exercises.css') : 'assets/css/pages/dynamic-exercises.css'; ?>\">" },
    @{ Old = "(?s)<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src=\"<\?php echo asset_url\('assets/js/interactive-exercises\.js'\); \?>\"></script>\s*<\?php else: \?>\s*<script src=\"/assets/js/interactive-exercises\.js\"></script>\s*<\?php endif; \?>"; New = "<script src=\"<?php echo function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : 'assets/js/interactive-exercises.js'; ?>\"></script>" },
    @{ Old = "(?s)<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src=\"<\?php echo asset_url\('assets/js/dynamic-exercises\.js'\); \?>\"></script>\s*<\?php else: \?>\s*<script src=\"/assets/js/dynamic-exercises\.js\"></script>\s*<\?php endif; \?>"; New = "<script src=\"<?php echo function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js'; ?>\"></script>" },
    @{ Old = "(?s)<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src=\"<\?php echo asset_url\('assets/js/exercises\.js'\); \?>\"></script>\s*<\?php else: \?>\s*<script src=\"/assets/js/exercises\.js\"></script>\s*<\?php endif; \?>"; New = "<script src=\"<?php echo function_exists('asset_url') ? asset_url('assets/js/exercises.js') : 'assets/js/exercises.js'; ?>\"></script>" },
    @{ Old = "(?s)<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src=\"<\?php echo asset_url\('assets/js/coach-webm\.js'\); \?>\"></script>\s*<\?php else: \?>\s*<script src=\"/assets/js/coach-webm\.js\"></script>\s*<\?php endif; \?>"; New = "<script src=\"<?php echo function_exists('asset_url') ? asset_url('assets/js/coach-webm.js') : 'assets/js/coach-webm.js'; ?>\"></script>" }
)

$changedFiles = @()
foreach ($file in $targets) {
    $content = Get-Content $file.FullName -Raw
    $original = $content
    foreach ($r in $replacements) {
        $content = [regex]::Replace($content, $r.Old, $r.New)
    }
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8
        $changedFiles += $file.FullName
    }
}
"CHANGED_FILES=$($changedFiles.Count)"
$changedFiles
