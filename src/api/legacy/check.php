<?php

/**
 * API Legacy - Endpoint fermé
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/deprecated.php';

api_deprecated(null);
json_error('Endpoint obsolète. Merci d’utiliser la nouvelle API.', 410, 'ERR_GONE');
