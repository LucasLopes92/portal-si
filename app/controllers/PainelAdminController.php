<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/PainelAdmin.php';

final class PainelAdminController
{
    public function __construct(private PainelAdmin $model)
    {
    }

    public function resumo(): array
    {
        return $this->model->obterResumo();
    }
}
