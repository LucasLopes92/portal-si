<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/AuthService.php';

/**
 * Controller do módulo de autenticação.
 *
 * Recebe os dados HTTP, chama o Service e devolve resultados para as Views.
 * Redirecionamentos e respostas HTTP ficam centralizados aqui.
 */
final class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function cadastrar(array $dados): array
    {
        return $this->authService->cadastrar($dados);
    }

    public function login(array $dados): bool
    {
        return $this->authService->autenticar(
            (string) ($dados['email'] ?? ''),
            (string) ($dados['senha'] ?? '')
        );
    }

    public function logout(): void
    {
        $this->authService->encerrarSessao();
    }
}
