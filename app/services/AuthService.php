<?php

declare(strict_types=1);

require_once __DIR__ . '/../helpers/validacao.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Regras de negócio da autenticação.
 *
 * O Service valida os dados, gera e confere hashes, controla a sessão e
 * transforma erros técnicos do Model em resultados compreensíveis para o
 * Controller.
 */
final class AuthService
{
    public function __construct(private Usuario $usuarioModel)
    {
    }

    /** Valida e cadastra uma conta comum do portal. */
    public function cadastrar(array $dados): array
    {
        $validacao = validar_cadastro_usuario($dados);

        if (!$validacao['valido']) {
            return [
                'sucesso' => false,
                'erros' => $validacao['erros'],
                'dados' => $validacao['dados'],
            ];
        }

        $dadosNormalizados = $validacao['dados'];
        $senhaHash = password_hash($dadosNormalizados['senha'], PASSWORD_DEFAULT);

        try {
            $id = $this->usuarioModel->criar(
                $dadosNormalizados['nome'],
                $dadosNormalizados['email'],
                $senhaHash,
                'aluno'
            );
        } catch (PDOException $exception) {
            // 23505 é a violação de UNIQUE do PostgreSQL: neste caso,
            // informamos duplicidade sem expor o SQL ou detalhes internos.
            if ($exception->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'erros' => ['email' => 'Este endereço de e-mail já está cadastrado.'],
                    'dados' => $dadosNormalizados,
                ];
            }

            error_log('Falha ao cadastrar usuário: ' . $exception->getMessage());

            return [
                'sucesso' => false,
                'erros' => ['geral' => 'Não foi possível concluir o cadastro.'],
                'dados' => $dadosNormalizados,
            ];
        }

        return ['sucesso' => true, 'id' => $id, 'erros' => [], 'dados' => []];
    }

    /** Confere credenciais e cria o contexto seguro de sessão. */
    public function autenticar(string $email, string $senha): bool
    {
        $email = trim($email);

        if ($email === '' || $senha === '') {
            return false;
        }

        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if ($usuario === null || !password_verify($senha, $usuario['senha_hash'])) {
            return false;
        }

        iniciar_sessao_segura();
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario_nome'] = (string) $usuario['nome'];
        $_SESSION['usuario_email'] = (string) $usuario['email'];
        $_SESSION['usuario_perfil'] = (string) $usuario['perfil'];
        $_SESSION['ultimo_acesso'] = time();

        return true;
    }

    /** Encerra sessão, cookie de transporte e dados armazenados no servidor. */
    public function encerrarSessao(): void
    {
        iniciar_sessao_segura();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => (bool) $params['secure'],
                'httponly' => (bool) $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }
}
