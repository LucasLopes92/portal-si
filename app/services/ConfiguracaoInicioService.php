<?php
declare(strict_types=1);
require_once __DIR__ . '/../models/ConfiguracaoInicio.php';
require_once __DIR__ . '/ImagemUploadService.php';

final class ConfiguracaoInicioService
{
    public function __construct(private ConfiguracaoInicio $model) {}

    public function salvar(array $entrada, array $arquivo, int $autor): array
    {
        $atual = $this->model->obter();
        $dados = $atual;
        $erros = [];
        $rotulos = ['etiqueta'=>'A chamada', 'titulo'=>'O título', 'descricao'=>'O texto de apresentação', 'botao_texto'=>'O texto do botão', 'imagem_alt'=>'A descrição da imagem'];
        foreach (['etiqueta'=>100, 'titulo'=>140, 'descricao'=>300, 'botao_texto'=>50, 'imagem_alt'=>180] as $campo=>$limite) {
            $dados[$campo] = is_string($entrada[$campo] ?? null) ? trim($entrada[$campo]) : '';
            if (mb_strlen($dados[$campo]) > $limite) { $erros[$campo] = $rotulos[$campo] . " deve ter até {$limite} caracteres."; }
        }
        foreach (['titulo'=>'Informe o título.', 'botao_texto'=>'Informe o texto do botão.', 'imagem_alt'=>'Descreva a imagem para quem utiliza leitor de tela.'] as $campo=>$mensagem) {
            if ($dados[$campo] === '') { $erros[$campo] = $mensagem; }
        }
        $destino = $entrada['botao_destino'] ?? '';
        if (!is_string($destino) || !in_array($destino, ['publicacoes', 'noticias', 'eventos'], true)) {
            $erros['botao_destino'] = 'Selecione um destino válido para o botão.';
        } else { $dados['botao_destino'] = $destino; }
        $posicao = filter_var($entrada['imagem_posicao'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($posicao) || $posicao < 0 || $posicao > 100) {
            $erros['imagem_posicao'] = 'A posição da imagem deve estar entre 0 e 100.';
        } else { $dados['imagem_posicao'] = $posicao; }
        if ($erros !== []) { return ['sucesso'=>false, 'dados'=>$dados, 'erros'=>$erros]; }

        $upload = new ImagemUploadService('inicio');
        $novas = [];
        try {
            if ($arquivo !== []) {
                if (!is_string($arquivo['name'] ?? null)) { throw new RuntimeException('Selecione uma única imagem para a abertura.'); }
                $lote = [];
                foreach (['name', 'tmp_name', 'error', 'size', 'type'] as $campo) { $lote[$campo] = [$arquivo[$campo] ?? null]; }
                $novas = $upload->receber($lote);
            }
            if ($novas !== []) { $dados['imagem'] = $novas[0]; }
            $this->model->salvar($dados, $autor);
        } catch (Throwable $erro) {
            $upload->remover($novas);
            $dados['imagem'] = $atual['imagem'];
            if ($erro instanceof PDOException) {
                error_log('Falha ao salvar configuração da Home: ' . $erro->getMessage());
                $mensagem = 'Não foi possível salvar as alterações. Tente novamente.';
            } else { $mensagem = $erro->getMessage(); }
            return ['sucesso'=>false, 'dados'=>$dados, 'erros'=>['geral'=>$mensagem]];
        }
        if ($novas !== []) { $upload->remover([$atual['imagem']]); }
        return ['sucesso'=>true, 'dados'=>$dados, 'erros'=>[]];
    }
}
