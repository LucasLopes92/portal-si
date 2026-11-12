<?php

declare(strict_types=1);

require_once __DIR__ . '/ImagemUploadService.php';

/**
 * Guarda na sessão as imagens enviadas antes de salvar a publicação.
 * Assim o formulário mostra a prévia e permite remover imagens sem JavaScript,
 * e as imagens não se perdem quando a validação falha.
 */
final class ImagensPendentesService
{
    private const CHAVE = 'imagens_pendentes';
    private const VALIDADE = 86400;
    private ImagemUploadService $temp;

    public function __construct()
    {
        $this->temp = new ImagemUploadService('temp');
    }

    /** Reaproveita o lote do formulário ou cria um novo. */
    public function lote(mixed $lote): string
    {
        return is_string($lote) && preg_match('/^[a-f0-9]{32}$/D', $lote) === 1 ? $lote : bin2hex(random_bytes(16));
    }

    /** @return list<string> */
    public function listar(string $lote): array
    {
        $caminhos = $_SESSION[self::CHAVE][$lote] ?? [];

        return array_values(array_filter(
            is_array($caminhos) ? $caminhos : [],
            fn (mixed $caminho): bool => $this->temp->gerenciado($caminho) && is_file(dirname(__DIR__, 2) . '/' . $caminho)
        ));
    }

    /**
     * Remove as imagens marcadas e recebe os novos arquivos do formulário.
     * Retorna a mensagem de erro do envio ou null.
     */
    public function atualizar(string $lote, mixed $remover, array $arquivos, int $existentes): ?string
    {
        $remover = is_array($remover) ? $remover : [];
        $manter = [];
        $descartar = [];
        foreach ($this->listar($lote) as $indice => $caminho) {
            if (in_array((string) $indice, $remover, true)) {
                $descartar[] = $caminho;
            } else {
                $manter[] = $caminho;
            }
        }
        $this->temp->remover($descartar);
        $this->temp->limparAntigos(self::VALIDADE);

        $erro = null;
        $novas = [];
        try {
            $novas = $this->temp->receber($arquivos, $existentes + count($manter));
        } catch (RuntimeException $exception) {
            $erro = $exception->getMessage();
        }
        $_SESSION[self::CHAVE][$lote] = array_merge($manter, $novas);

        return $erro;
    }

    public function origem(): ImagemUploadService
    {
        return $this->temp;
    }

    /** Esquece o lote. Arquivos que ainda estiverem na pasta temporária são apagados. */
    public function limpar(string $lote): void
    {
        $this->temp->remover($this->listar($lote));
        unset($_SESSION[self::CHAVE][$lote]);
    }
}
