<?php

declare(strict_types=1);

/**
 * Reutiliza a conexão central do Portal SI sem duplicar credenciais dentro do
 * módulo administrativo.
 */
require_once __DIR__ . '/../../config/database.php';
