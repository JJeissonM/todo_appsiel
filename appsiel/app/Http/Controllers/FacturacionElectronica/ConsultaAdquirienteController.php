<?php

namespace App\Http\Controllers\FacturacionElectronica;

use App\Core\Tercero;
use App\FacturacionElectronica\OSEI\ConsultaAdquiriente;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConsultaAdquirienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function consultar($tipo, $numero)
    {
        if (!preg_match('/^\d{2}$/', $tipo) || !preg_match('/^[A-Za-z0-9-]{3,20}$/', $numero)) {
            return response()->json(['status' => ConsultaAdquiriente::NO_DISPONIBLE]);
        }

        $tercero = Tercero::where('numero_identificacion', $numero)
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->first();

        if (!is_null($tercero)) {
            return response()->json(['status' => 'existe', 'name' => $tercero->descripcion]);
        }

        return response()->json((new ConsultaAdquiriente())->consultar($tipo, $numero));
    }
}
