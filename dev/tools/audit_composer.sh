#!/bin/bash
# audit_composer.sh — Audit dépendances Composer
# Usage : bash audit_composer.sh

set -e

# Validation composer.json/lock
composer validate

# Audit sécurité dépendances
composer audit || true
