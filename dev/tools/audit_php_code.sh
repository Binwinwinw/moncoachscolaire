#!/bin/bash
# audit_php_code.sh — Audit statique PHP (PHPStan + Psalm)
# Usage : bash audit_php_code.sh

set -e

# PHPStan
if command -v phpstan > /dev/null; then
  echo "[PHPStan] Analyse en cours..."
  phpstan analyse src/ --level=max || true
else
  echo "[PHPStan] Non installé. Installer avec : composer require --dev phpstan/phpstan"
fi

# Psalm
if command -v psalm > /dev/null; then
  echo "[Psalm] Analyse en cours..."
  psalm --show-info=false || true
else
  echo "[Psalm] Non installé. Installer avec : composer require --dev vimeo/psalm"
fi
