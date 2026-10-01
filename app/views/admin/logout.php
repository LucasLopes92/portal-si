<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Logout não depende de consulta ao banco: a rotina invalida a sessão e o
// cookie diretamente no Service, depois retorna ao formulário de login.
$controller = new AuthController(new AuthService(new Usuario($pdo)));
$controller->logout();

header('Location: login.php?msg=desconectado');
exit;
