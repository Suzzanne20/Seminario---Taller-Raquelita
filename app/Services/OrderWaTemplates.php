<?php

namespace App\Services;

use App\Models\OrdenTrabajo;
use Illuminate\Support\Str;

class OrderWaTemplates
{
    public static function created(OrdenTrabajo $ot): string
    {
        $placa = $ot->vehiculo_placa;
        $id    = $ot->id;
        $desc  = $ot->servicio?->descripcion ?: 'Servicio';
        return "🚗🔧 *Taller Raquelita*  ✅ \n".
                " \n".
               "Tu Orden de Trabajo *#{$id}* fue creada.\n".
               "Vehículo placa: *{$placa}* \n".
               "Servicio: {$desc}.\n".
                " \n".
               "Gracias por preferirnos. 🛠️";
    }

    public static function statusChanged(OrdenTrabajo $ot, string $oldName, string $newName): string
    {
        $id   = $ot->id;
        $placa= $ot->vehiculo_placa;
        return "🔔 *Actualización de OT*\n".
               "Orden *#{$id}* (placa *{$placa}*):\n".
               "Estado: *{$oldName} ➜ {$newName}*.\n".
               "¡Te avisaremos de nuevos avances!";
    }
}
