<?php

namespace App\Services\Ordenes;

class GestorEstadosOT
{
    private array $transiciones = [
        'CREADA'      => ['EN_PROCESO', 'CANCELADA'],
        'EN_PROCESO'  => ['FINALIZADA', 'CANCELADA'],
        'FINALIZADA'  => ['ENTREGADA'],
        'ENTREGADA'   => [],
        'CANCELADA'   => [],
    ];

    public function puedeTransicionar(string $actual, string $nuevo): bool
    {
        $actual = strtoupper($actual);
        $nuevo  = strtoupper($nuevo);
        return in_array($nuevo, $this->transiciones[$actual] ?? [], true);
    }
}
