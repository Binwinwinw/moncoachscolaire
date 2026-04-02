<?php

/**
 * API Core Validators
 */

function validate_allowed_fields(array $data, array $allowed): void
{
    $extra = array_diff(array_keys($data), $allowed);
    if (!empty($extra)) {
        json_error('Paramètres inattendus', 422, 'ERR_VALIDATION');
    }
}

function validate_required_fields(array $data, array $required): void
{
    foreach ($required as $field) {
        if (!array_key_exists($field, $data)) {
            json_error('Paramètres manquants', 422, 'ERR_VALIDATION');
        }
    }
}

function validate_int($value, string $field, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
{
    if (!is_numeric($value)) {
        json_error('Paramètre invalide: ' . $field, 422, 'ERR_VALIDATION');
    }
    $intVal = (int) $value;
    if ($intVal < $min || $intVal > $max) {
        json_error('Paramètre hors limites: ' . $field, 422, 'ERR_VALIDATION');
    }
    return $intVal;
}

function validate_string($value, string $field, int $maxLen = 255): string
{
    if (!is_string($value)) {
        json_error('Paramètre invalide: ' . $field, 422, 'ERR_VALIDATION');
    }
    $value = trim($value);
    if (mb_strlen($value) > $maxLen) {
        json_error('Paramètre trop long: ' . $field, 422, 'ERR_VALIDATION');
    }
    return $value;
}

function validate_enum($value, string $field, array $allowed): string
{
    if (!in_array($value, $allowed, true)) {
        json_error('Paramètre invalide: ' . $field, 422, 'ERR_VALIDATION');
    }
    return (string) $value;
}

function validate_bool($value, string $field): bool
{
    if (is_bool($value)) {
        return $value;
    }
    if ($value === 1 || $value === 0 || $value === '1' || $value === '0') {
        return (bool) $value;
    }
    json_error('Paramètre invalide: ' . $field, 422, 'ERR_VALIDATION');
}
