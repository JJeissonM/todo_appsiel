<?php

namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoChequera;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMedioRecaudoDestino;
use Illuminate\Http\Request;

class TesoMedioRecaudoDestinoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, $teso_medio_recaudo_id)
    {
        $medioRecaudo = TesoMedioRecaudo::find($teso_medio_recaudo_id);
        if (is_null($medioRecaudo)) {
            return redirect()->back()->with('mensaje_error', 'El medio de recaudo no existe.');
        }

        $usaCuentaBancaria = $medioRecaudo->usa_cuenta_bancaria_como_destino();
        $tesoCajaId = null;
        $tesoCuentaBancariaId = null;

        if ($usaCuentaBancaria) {
            $tesoCuentaBancariaId = (int)$request->get('teso_cuenta_bancaria_id');
            if ($tesoCuentaBancariaId === 0 || !TesoCuentaBancaria::es_permitida_para_usuario($tesoCuentaBancariaId)) {
                return redirect()->back()->with('mensaje_error', 'Debe seleccionar una cuenta bancaria válida.');
            }

            if ($medioRecaudo->comportamiento === 'Cheque'
                && !TesoChequera::where('teso_cuenta_bancaria_id', $tesoCuentaBancariaId)->exists()) {
                return redirect()->back()->with('mensaje_error', 'La cuenta bancaria debe tener una chequera asociada.');
            }
        } else {
            $tesoCajaId = (int)$request->get('teso_caja_id');
            if ($tesoCajaId === 0 || is_null(TesoCaja::find($tesoCajaId))) {
                return redirect()->back()->with('mensaje_error', 'Debe seleccionar una caja válida.');
            }
        }

        $yaExiste = TesoMedioRecaudoDestino::where('teso_medio_recaudo_id', $medioRecaudo->id)
            ->where('teso_caja_id', $tesoCajaId)
            ->where('teso_cuenta_bancaria_id', $tesoCuentaBancariaId)
            ->exists();

        if ($yaExiste) {
            return redirect()->back()->with('mensaje_error', 'La relación ya existe.');
        }

        TesoMedioRecaudoDestino::create([
            'teso_medio_recaudo_id' => $medioRecaudo->id,
            'teso_caja_id' => $tesoCajaId,
            'teso_cuenta_bancaria_id' => $tesoCuentaBancariaId,
            'estado' => 'Activo'
        ]);

        return redirect()->back()->with('flash_message', 'Relación creada correctamente.');
    }

    public function destroy($teso_medio_recaudo_id, $id)
    {
        $destino = TesoMedioRecaudoDestino::where('teso_medio_recaudo_id', $teso_medio_recaudo_id)
            ->where('id', $id)
            ->first();

        if (is_null($destino)) {
            return redirect()->back()->with('mensaje_error', 'La relación no existe.');
        }

        $destino->delete();

        return redirect()->back()->with('flash_message', 'Relación eliminada correctamente.');
    }
}
