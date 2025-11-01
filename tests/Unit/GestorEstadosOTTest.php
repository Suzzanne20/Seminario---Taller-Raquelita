<?php

namespace Tests\Unit;

use App\Services\Ordenes\GestorEstadosOT;
use PHPUnit\Framework\TestCase;

class GestorEstadosOTTest extends TestCase
{
    public function test_transiciones_validas()
    {
        $g = new GestorEstadosOT();
        $this->assertTrue($g->puedeTransicionar('CREADA', 'EN_PROCESO'));
        $this->assertTrue($g->puedeTransicionar('EN_PROCESO', 'FINALIZADA'));
        $this->assertTrue($g->puedeTransicionar('FINALIZADA', 'ENTREGADA'));
    }

    public function test_transiciones_invalidas()
    {
        $g = new GestorEstadosOT();
        $this->assertFalse($g->puedeTransicionar('ENTREGADA', 'CREADA'));
        $this->assertFalse($g->puedeTransicionar('CANCELADA', 'EN_PROCESO'));
        $this->assertFalse($g->puedeTransicionar('CREADA', 'ENTREGADA'));
    }
}
