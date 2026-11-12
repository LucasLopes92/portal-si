<?php
declare(strict_types=1);
require_once __DIR__ . '/../services/ConfiguracaoInicioService.php';

final class ConfiguracaoInicioController
{
    public function __construct(private ConfiguracaoInicio $model) {}
    public function obter(): array { return $this->model->obter(); }
    public function salvar(array $dados, array $imagem, int $autor): array
    {
        return (new ConfiguracaoInicioService($this->model))->salvar($dados, $imagem, $autor);
    }
}
