<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ConteudoAdmin.php';
require_once __DIR__ . '/../helpers/publico.php';
require_once __DIR__ . '/ImagemUploadService.php';

final class ConteudoAdminService
{
    private const STATUS_PERMITIDOS = ['rascunho', 'publicado', 'arquivado'];

    public function __construct(private ConteudoAdmin $model)
    {
    }

    public function criar(array $entrada, int $autorId, array $arquivos = []): array
    {
        $validacao = $this->validar($entrada);
        if ($validacao['erros'] !== []) {
            return [
                'sucesso' => false,
                'erros' => $validacao['erros'],
                'dados' => $validacao['dados'],
            ];
        }

        $dados = $validacao['dados'];
        $dados['slug'] = $this->gerarSlugUnico($dados['titulo']);
        $dados['autor_id'] = $autorId;
        $dados['publicado_em'] = $dados['status'] === 'publicado' ? date(DATE_ATOM) : null;

        $upload = new ImagemUploadService();
        try {
            $novas = $upload->receber($arquivos);
        } catch (RuntimeException $erro) {
            return ['sucesso' => false, 'erros' => ['imagens' => $erro->getMessage()], 'dados' => $dados];
        }
        $dados['imagens'] = $novas;
        $dados['imagem_capa'] = $novas[0] ?? $dados['imagem_capa'];

        try {
            $id = $this->model->criar($dados);
        } catch (PDOException $exception) {
            $upload->remover($novas);
            error_log('Falha ao cadastrar conteúdo administrativo: ' . $exception->getMessage());

            return [
                'sucesso' => false,
                'erros' => ['geral' => 'Não foi possível cadastrar o conteúdo.'],
                'dados' => $dados,
            ];
        }

        return ['sucesso' => true, 'id' => $id, 'erros' => [], 'dados' => $dados];
    }

    public function atualizar(int $id, array $entrada, array $arquivos = []): array
    {
        $atual = $this->model->buscarPorId($id);
        if ($atual === null) {
            return [
                'sucesso' => false,
                'nao_encontrado' => true,
                'erros' => ['geral' => 'Conteúdo não encontrado.'],
                'dados' => [],
            ];
        }

        // As referências existentes são obtidas do banco, nunca do formulário.
        $entrada['imagem_capa'] = $atual['imagem_capa'] ?? '';
        $validacao = $this->validar($entrada);
        if ($validacao['erros'] !== []) {
            return [
                'sucesso' => false,
                'nao_encontrado' => false,
                'erros' => $validacao['erros'],
                'dados' => $validacao['dados'],
            ];
        }

        $dados = $validacao['dados'];
        $dados['slug'] = $this->gerarSlugUnico($dados['titulo'], $id);
        $dados['publicado_em'] = $dados['status'] === 'publicado'
            ? ($atual['publicado_em'] ?: date(DATE_ATOM))
            : null;

        $anteriores = imagens_publicacao($atual);
        $remover = is_array($entrada['remover_imagens'] ?? null) ? $entrada['remover_imagens'] : [];
        $mantidas = [];
        $removidas = [];
        foreach ($anteriores as $indice => $caminho) {
            if (in_array((string) $indice, $remover, true)) {
                $removidas[] = $caminho;
            } else {
                $mantidas[] = $caminho;
            }
        }
        $upload = new ImagemUploadService();
        try {
            $novas = $upload->receber($arquivos, count($mantidas));
        } catch (RuntimeException $erro) {
            return ['sucesso' => false, 'nao_encontrado' => false, 'erros' => ['imagens' => $erro->getMessage()], 'dados' => $dados];
        }
        $dados['imagens'] = array_merge($mantidas, $novas);
        $dados['imagem_capa'] = $dados['imagens'][0] ?? null;

        try {
            $atualizado = $this->model->atualizar($id, $dados);
        } catch (PDOException $exception) {
            $upload->remover($novas);
            error_log('Falha ao atualizar conteúdo administrativo: ' . $exception->getMessage());

            return [
                'sucesso' => false,
                'nao_encontrado' => false,
                'erros' => ['geral' => 'Não foi possível atualizar o conteúdo.'],
                'dados' => $dados,
            ];
        }

        if ($atualizado) {
            $upload->remover($removidas);
        } else {
            $upload->remover($novas);
        }

        return [
            'sucesso' => $atualizado,
            'nao_encontrado' => false,
            'erros' => $atualizado ? [] : ['geral' => 'Nenhuma alteração foi realizada.'],
            'dados' => $dados,
        ];
    }

    public function excluir(int $id): array
    {
        $conteudo = $this->model->buscarPorId($id);
        if ($conteudo === null) {
            return [
                'sucesso' => false,
                'nao_encontrado' => true,
                'erro' => 'Conteúdo não encontrado.',
            ];
        }

        try {
            $excluido = $this->model->excluir($id);
        } catch (PDOException $exception) {
            error_log('Falha ao excluir conteúdo administrativo: ' . $exception->getMessage());

            return [
                'sucesso' => false,
                'nao_encontrado' => false,
                'erro' => 'Não foi possível excluir o conteúdo.',
            ];
        }

        if ($excluido) {
            (new ImagemUploadService())->remover(imagens_publicacao($conteudo));
        }

        return [
            'sucesso' => $excluido,
            'nao_encontrado' => !$excluido,
            'erro' => $excluido ? null : 'Conteúdo não encontrado.',
        ];
    }

    private function validar(array $entrada): array
    {
        $titulo = trim((string) ($entrada['titulo'] ?? ''));
        $categoriaId = filter_var($entrada['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        $resumo = trim((string) ($entrada['resumo'] ?? ''));
        $corpo = trim((string) ($entrada['corpo'] ?? ''));
        $linkYoutube = trim((string) ($entrada['link_youtube'] ?? ''));
        $imagemCapa = trim((string) ($entrada['imagem_capa'] ?? ''));
        $status = (string) ($entrada['status'] ?? 'rascunho');
        $destaque = isset($entrada['destaque']) && (string) $entrada['destaque'] === '1';
        $erros = [];

        if ($titulo === '') {
            $erros['titulo'] = 'Informe o título do conteúdo.';
        } elseif (mb_strlen($titulo) > 200) {
            $erros['titulo'] = 'O título deve ter no máximo 200 caracteres.';
        }

        if (!is_int($categoriaId) || $categoriaId <= 0 || !$this->model->categoriaAtivaExiste($categoriaId)) {
            $erros['categoria_id'] = 'Selecione uma categoria ativa.';
        }

        if (mb_strlen($resumo) > 500) {
            $erros['resumo'] = 'O resumo deve ter no máximo 500 caracteres.';
        }

        if ($corpo === '') {
            $erros['corpo'] = 'Informe o texto completo do conteúdo.';
        }

        if ($linkYoutube !== '' && !$this->urlYoutubeValida($linkYoutube)) {
            $erros['link_youtube'] = 'Informe uma URL válida do YouTube ou deixe o campo vazio.';
        }

        if (mb_strlen($imagemCapa) > 255 || ($imagemCapa !== '' && imagem_publica_src($imagemCapa) === null)) {
            $erros['imagem_capa'] = 'Informe uma URL HTTPS ou um caminho relativo vÃ¡lido para a imagem (atÃ© 255 caracteres).';
        }

        if (!in_array($status, self::STATUS_PERMITIDOS, true)) {
            $erros['status'] = 'Selecione um status válido.';
            $status = 'rascunho';
        }

        return [
            'erros' => $erros,
            'dados' => [
                'titulo' => $titulo,
                'categoria_id' => is_int($categoriaId) ? $categoriaId : 0,
                'resumo' => $resumo === '' ? null : $resumo,
                'corpo' => $corpo,
                'link_youtube' => $linkYoutube === '' ? null : $linkYoutube,
                'imagem_capa' => $imagemCapa === '' ? null : $imagemCapa,
                'status' => $status,
                'destaque' => $destaque,
            ],
        ];
    }

    private function urlYoutubeValida(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'], true);
    }

    private function gerarSlugUnico(string $titulo, ?int $ignorarId = null): string
    {
        $transliterado = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $titulo);
        $base = strtolower($transliterado !== false ? $transliterado : $titulo);
        $base = trim((string) preg_replace('/[^a-z0-9]+/', '-', $base), '-');
        $base = $base !== '' ? mb_substr($base, 0, 200) : 'conteudo';
        $slug = $base;
        $sufixo = 2;

        while ($this->model->slugExiste($slug, $ignorarId)) {
            $final = '-' . $sufixo;
            $slug = mb_substr($base, 0, 220 - mb_strlen($final)) . $final;
            $sufixo++;
        }

        return $slug;
    }
}
