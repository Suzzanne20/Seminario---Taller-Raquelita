<?php

namespace Tests\Unit;

use App\Services\Ordenes\CalculadorOT;
use PHPUnit\Framework\TestCase;

class CalculadorOTTest extends TestCase
{
    private function itemsBase(): array
    {
        return [
            ['descripcion' => 'Mano de obra', 'cantidad' => 1, 'precio' => 250.00],
            ['descripcion' => 'Aceite',       'cantidad' => 2, 'precio' => 75.50],   // 151.00
            ['descripcion' => 'Filtro',       'cantidad' => 1, 'precio' => 49.99],
        ];
        // Subtotal esperado: 250 + 151 + 49.99 = 450.99
    }

    public function test_subtotal_y_total_sin_descuento_ni_extras()
    {
        $svc = new CalculadorOT();
        $res = $svc->calcular($this->itemsBase(), 0, 12, 0, 0);

        $this->assertSame(450.99, $res['subtotal']);
        $this->assertSame(0.0, $res['descuento']);
        // Base imponible = 450.99
        // IVA 12% = 54.12 (450.99 * 0.12 = 54.1188 → 54.12)
        $this->assertSame(54.12, $res['impuesto']);
        $this->assertSame(505.11, $res['total']); // 450.99 + 54.12
    }

    public function test_descuento_aplicado_dentro_de_rango()
    {
        $svc = new CalculadorOT();
        $res = $svc->calcular($this->itemsBase(), 10, 12, 0, 0);

        // Subtotal 450.99; 10% = 45.10
        $this->assertSame(45.10, $res['descuento']);
        // Base = 405.89; IVA 12% = 48.71; Total = 454.60
        $this->assertSame(48.71, $res['impuesto']);
        $this->assertSame(454.60, $res['total']);
    }

    public function test_descuento_se_topea_a_30_por_ciento()
    {
        $svc = new CalculadorOT();
        $res = $svc->calcular($this->itemsBase(), 80, 12, 0, 0); // pide 80%, pero se topea a 30%

        // 30% de 450.99 = 135.30
        $this->assertSame(135.30, $res['descuento']);
        // Base = 315.69; IVA 12% = 37.88; Total = 353.57
        $this->assertSame(37.88, $res['impuesto']);
        $this->assertSame(353.57, $res['total']);
    }

    public function test_con_envio_y_comision()
    {
        $svc = new CalculadorOT();
        $res = $svc->calcular($this->itemsBase(), 5, 12, 25.00, 10.00);
        // Subtotal 450.99; desc 5% = 22.55; base = 450.99 - 22.55 + 25 + 10 = 463.44
        // IVA 12% = 55.61; total = 519.05
        $this->assertSame(22.55, $res['descuento']);
        $this->assertSame(55.61, $res['impuesto']);
        $this->assertSame(519.05, $res['total']);
    }

    public function test_items_invalidos_no_afectan_subtotal()
    {
        $svc = new CalculadorOT();
        $items = array_merge($this->itemsBase(), [
            ['descripcion'=>'Renglon inválido', 'cantidad'=>0, 'precio'=>999],
            ['descripcion'=>'Negativo', 'cantidad'=>-2, 'precio'=>100],
        ]);
        $res = $svc->calcular($items, 0, 12, 0, 0);
        $this->assertSame(450.99, $res['subtotal']); // iguales al caso base
    }

    public function test_lista_vacia_da_total_cero()
    {
        $svc = new CalculadorOT();
        $res = $svc->calcular([], 20, 12, 50, 30);
        $this->assertSame(0.0, $res['subtotal']);
        $this->assertSame(0.0, $res['descuento']); // descuento sobre 0
        // Base = 0 - 0 + 50 + 30 = 80; IVA = 9.6 → 9.60; Total = 89.60
        $this->assertSame(9.60, $res['impuesto']);
        $this->assertSame(89.60, $res['total']);
    }
}
