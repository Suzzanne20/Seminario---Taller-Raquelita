<?php

namespace App\Services\Ordenes;

class CalculadorOT
{
    /**
     * Calcula totales de una Orden de Trabajo.
     *
     * @param array $items  Lista de renglones: [['descripcion'=>'...', 'cantidad'=>2, 'precio'=>150.00], ...]
     * @param float $descuentoPorc  Descuento en % (0..30). Se topea a 30 para evitar abusos.
     * @param float $impuestoPorc   IVA en % (ej. 12 para 12%).
     * @param float $envio          Costos adicionales (opcional).
     * @param float $comision       Comisiones (opcional).
     * @return array ['subtotal','descuento','impuesto','total']
     */
    public function calcular(array $items, float $descuentoPorc = 0, float $impuestoPorc = 12, float $envio = 0, float $comision = 0): array
    {
        // 1) Subtotal (suma de cantidad*precio, ignorando renglones inválidos)
        $subtotal = 0.0;
        foreach ($items as $it) {
            $cant = (float)($it['cantidad'] ?? 0);
            $prec = (float)($it['precio'] ?? 0);
            if ($cant > 0 && $prec >= 0) {
                $subtotal += $cant * $prec;
            }
        }

        // 2) Tope al descuento (caja blanca: rama con tope)
        $descuentoPorc = max(0, min($descuentoPorc, 30));
        $descuento = round($subtotal * ($descuentoPorc / 100), 2);

        // 3) Base imponible = subtotal - descuento + extras
        $baseImponible = max(0, $subtotal - $descuento + $envio + $comision);

        // 4) Impuesto
        $impuesto = round($baseImponible * ($impuestoPorc / 100), 2);

        // 5) Total
        $total = round($baseImponible + $impuesto, 2);

        // Redondeos finales homogéneos
        return [
            'subtotal'  => round($subtotal, 2),
            'descuento' => $descuento,
            'impuesto'  => $impuesto,
            'total'     => $total,
        ];
    }
}
