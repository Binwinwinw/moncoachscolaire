<?php
// scripts/composer-post-install.php
// Composer post-install hook: ensure a .env exists by copying .env.example
$root = dirname(__DIR__);
$example = $root . DIRECTORY_SEPARATOR . '.env.example';
$env = $root . DIRECTORY_SEPARATOR . '.env';
if (!file_exists($env) && file_exists($example)) {
    copy($example, $env);
    echo "Created .env from .env.example\n";
} else {
    echo ".env already exists or .env.example missing\n";
}
