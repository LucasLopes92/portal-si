<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AutenticacaoAdminService.php';

final class AutenticacaoAdminController
{
    public function __construct(private AutenticacaoAdminService $service)
    {
    }

    public function login(array $dados): bool
    {
        return $this->service->autenticar(
            (string) ($dados['email'] ?? ''),
            (string) ($dados['senha'] ?? '')
        );
    }

    public function logout(): void
    {
        $this->service->encerrar();
    }
}
