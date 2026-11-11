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

        $respuesta = (new ConsultaAdquiriente())->consultar($tipo, $numero);

        // El aviso de NIT con el DV pegado va primero: OSEI rechaza las facturas a ese número.
        // numero_identificacion es numérico, así que "901181352-1" coincide con 901181352.
        if (!empty($respuesta['warnings']) && in_array('nit_con_dv', $respuesta['warnings'])) {
            return response()->json($respuesta);
        }

        $tercero = Tercero::where('numero_identificacion', $numero)
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->first();

        if (!is_null($tercero)) {
            return response()->json(['status' => 'existe', 'name' => $tercero->descripcion]);
        }

        return response()->json($respuesta);
    }
}
