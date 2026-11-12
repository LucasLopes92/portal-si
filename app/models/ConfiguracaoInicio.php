<?php
declare(strict_types=1);

final class ConfiguracaoInicio
{
    public function __construct(private PDO $pdo) {}

    public function obter(): array
    {
        $dados = $this->pdo->query('SELECT * FROM configuracao_inicio WHERE id = 1')->fetch();
        if (!$dados) {
            throw new RuntimeException('Configuração da página inicial não encontrada.');
        }
        return $dados;
    }

    public function salvar(array $dados, int $autor): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE configuracao_inicio SET etiqueta=:etiqueta, titulo=:titulo,
             descricao=:descricao, botao_texto=:botao_texto, botao_destino=:botao_destino,
             imagem=:imagem, imagem_alt=:imagem_alt, imagem_posicao=:imagem_posicao,
             atualizado_em=clock_timestamp(), atualizado_por=:autor WHERE id=1'
        );
        $valores = array_intersect_key($dados, array_flip(['etiqueta', 'titulo', 'descricao', 'botao_texto', 'botao_destino', 'imagem', 'imagem_alt', 'imagem_posicao']));
        $valores['autor'] = $autor;
        $stmt->execute($valores);
        if ($stmt->rowCount() !== 1) { throw new RuntimeException('Não foi possível salvar a página inicial.'); }
    }
}
