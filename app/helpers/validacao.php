<?php

declare(strict_types=1);

/**
 * Validação server-side do cadastro.
 *
 * A validação do navegador melhora a experiência, mas não é suficiente: os
 * mesmos critérios são aplicados aqui, pois requisições podem ser enviadas
 * diretamente ao servidor por ferramentas externas.
 */
function validar_cadastro_usuario(array $dados): array
{
    $erros = [];
    $nome = trim((string) ($dados['nome'] ?? ''));
    $email = trim((string) ($dados['email'] ?? ''));
    $senha = (string) ($dados['senha'] ?? '');
    $confirmacao = (string) ($dados['confirmar_senha'] ?? $dados['confirma_senha'] ?? '');

    if (mb_strlen($nome) < 3) {
        $erros['nome'] = 'O nome deve conter no mínimo 3 caracteres.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Informe um endereço de e-mail válido.';
    }

    if (mb_strlen($senha) < 8) {
        $erros['senha'] = 'A senha deve conter no mínimo 8 caracteres.';
    }

    if (!hash_equals($senha, $confirmacao)) {
        $erros['confirmar_senha'] = 'A confirmação de senha não confere.';
    }

    return [
        'valido' => $erros === [],
        'erros' => $erros,
        'dados' => [
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha,
        ],
    ];
}
