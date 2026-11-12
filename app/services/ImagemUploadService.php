<?php

declare(strict_types=1);

final class ImagemUploadService
{
    public const MAX_IMAGENS = 10;
    public const MAX_BYTES = 2 * 1024 * 1024;
    private const MAX_TOTAL = 6 * 1024 * 1024;
    private string $prefixo;

    public function __construct(string $pasta = 'publicacoes')
    {
        if (!in_array($pasta, ['publicacoes', 'inicio', 'temp'], true)) {
            throw new InvalidArgumentException('Pasta de imagem inválida.');
        }
        $this->prefixo = 'public/uploads/' . $pasta . '/';
    }

    /** Valida todo o lote antes de mover qualquer arquivo. */
    public function receber(array $arquivos, int $existentes = 0): array
    {
        $nomes = $arquivos['name'] ?? [];
        if (!is_array($nomes)) {
            throw new RuntimeException('Selecione as imagens novamente.');
        }
        $validos = [];
        $total = 0;
        $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        foreach ($nomes as $i => $nome) {
            $erro = $arquivos['error'][$i] ?? UPLOAD_ERR_NO_FILE;
            if ($erro === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($erro !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Não foi possível enviar uma das imagens. Cada arquivo deve ter até 2 MB. Selecione as imagens novamente.');
            }
            $tmp = $arquivos['tmp_name'][$i] ?? '';
            if (!is_string($tmp) || !is_uploaded_file($tmp)) {
                throw new RuntimeException('O envio de uma das imagens é inválido.');
            }
            $bytes = filesize($tmp);
            $total += $bytes;
            if ($bytes <= 0 || $bytes > self::MAX_BYTES || $total > self::MAX_TOTAL) {
                throw new RuntimeException('Use imagens de até 2 MB e envie no máximo 6 MB por vez.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
            $info = @getimagesize($tmp);
            if (!isset($tipos[$mime]) || $info === false || ($info['mime'] ?? '') !== $mime) {
                throw new RuntimeException('Envie somente imagens JPG, PNG, WebP ou GIF válidas.');
            }
            if ($info[0] * $info[1] > 25000000) {
                throw new RuntimeException('Uma das imagens é muito grande. Use imagens de até 25 megapixels.');
            }
            $validos[] = [$tmp, $tipos[$mime]];
        }
        if ($existentes + count($validos) > self::MAX_IMAGENS) {
            throw new RuntimeException('Cada publicação pode ter até 10 imagens. Remova alguma antes de adicionar mais.');
        }
        $pasta = dirname(__DIR__, 2) . '/' . $this->prefixo;
        $salvos = [];
        try {
            if ($validos !== [] && !is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
                throw new RuntimeException('Não foi possível preparar a pasta das imagens.');
            }
            foreach ($validos as [$tmp, $extensao]) {
                $relativo = $this->prefixo . bin2hex(random_bytes(16)) . '.' . $extensao;
                if (!move_uploaded_file($tmp, dirname(__DIR__, 2) . '/' . $relativo)) {
                    throw new RuntimeException('Não foi possível salvar as imagens. Tente novamente.');
                }
                $salvos[] = $relativo;
            }
        } catch (Throwable $erro) {
            $this->remover($salvos);
            throw $erro;
        }
        return $salvos;
    }

    /**
     * Move para a pasta deste serviço imagens já enviadas para outra pasta
     * (por exemplo, a temporária). Só aceita arquivos gerados pelo upload.
     */
    public function adotar(array $caminhos, self $origem): array
    {
        $movidos = [];
        try {
            foreach ($caminhos as $caminho) {
                if (!$origem->gerenciado($caminho) || !is_file(dirname(__DIR__, 2) . '/' . $caminho)) {
                    throw new RuntimeException('Uma das imagens enviadas não está mais disponível. Envie-a novamente.');
                }
                $pasta = dirname(__DIR__, 2) . '/' . $this->prefixo;
                if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
                    throw new RuntimeException('Não foi possível preparar a pasta das imagens.');
                }
                $relativo = $this->prefixo . basename($caminho);
                if (!rename(dirname(__DIR__, 2) . '/' . $caminho, dirname(__DIR__, 2) . '/' . $relativo)) {
                    throw new RuntimeException('Não foi possível salvar as imagens. Tente novamente.');
                }
                $movidos[] = $relativo;
            }
        } catch (Throwable $erro) {
            $this->remover($movidos);
            throw $erro;
        }

        return $movidos;
    }

    /** Apaga arquivos desta pasta mais antigos que o limite (uso na pasta temporária). */
    public function limparAntigos(int $segundos): void
    {
        $pasta = dirname(__DIR__, 2) . '/' . $this->prefixo;
        foreach (glob($pasta . '*') ?: [] as $arquivo) {
            $caminho = $this->prefixo . basename($arquivo);
            if ($this->gerenciado($caminho) && is_file($arquivo) && filemtime($arquivo) < time() - $segundos) {
                @unlink($arquivo);
            }
        }
    }

    public function gerenciado(mixed $caminho): bool
    {
        return is_string($caminho)
            && preg_match('~^' . preg_quote($this->prefixo, '~') . '[a-f0-9]{32}\.(jpg|png|webp|gif)$~D', $caminho) === 1;
    }

    /** Só apaga arquivos gerados pelo próprio fluxo de upload. */
    public function remover(array $caminhos): void
    {
        foreach ($caminhos as $caminho) {
            if ($this->gerenciado($caminho)) {
                $arquivo = dirname(__DIR__, 2) . '/' . $caminho;
                if (is_file($arquivo) && !unlink($arquivo)) {
                    error_log('Não foi possível remover imagem de publicação: ' . basename($arquivo));
                }
            }
        }
    }
}
