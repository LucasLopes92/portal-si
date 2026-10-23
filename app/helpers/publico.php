<?php

declare(strict_types=1);

function imagem_publica_src(?string $caminho): ?string
{
    $caminho = trim((string) $caminho);
    if ($caminho === '') {
        return null;
    }

    if (filter_var($caminho, FILTER_VALIDATE_URL)) {
        return strtolower((string) parse_url($caminho, PHP_URL_SCHEME)) === 'https'
            ? $caminho
            : null;
    }

    if (str_starts_with($caminho, '//') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $caminho)) {
        return null;
    }

    return preg_match('~^(?!/)(?!.*(?:^|/)\.\.(?:/|$))[A-Za-z0-9_./% -]+$~', $caminho)
        ? $caminho
        : null;
}

function youtube_embed_url(?string $url): ?string
{
    $partes = parse_url(trim((string) $url));
    if (!is_array($partes) || empty($partes['host'])) {
        return null;
    }

    $host = strtolower((string) $partes['host']);
    $id = null;
    if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
        $id = trim((string) ($partes['path'] ?? ''), '/');
    } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
        if (($partes['path'] ?? '') === '/watch') {
            parse_str((string) ($partes['query'] ?? ''), $query);
            $id = $query['v'] ?? null;
        } elseif (preg_match('~^/(?:embed|shorts)/([^/]+)~', (string) ($partes['path'] ?? ''), $match)) {
            $id = $match[1];
        }
    }

    return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id)
        ? 'https://www.youtube-nocookie.com/embed/' . $id
        : null;
}
