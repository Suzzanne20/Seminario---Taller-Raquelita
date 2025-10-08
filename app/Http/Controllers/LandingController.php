<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenTrabajo;

class LandingController extends Controller
{
    public function home(Request $request)
    {
        $orden = null;

        if ($request->filled('ot')) {
            $id = (int) trim($request->ot);
            if ($id > 0) {
                $orden = OrdenTrabajo::with(['vehiculo','servicio','estado'])
                    ->find($id);
            }
        }
        return view('home', compact('orden'));
    }
}
