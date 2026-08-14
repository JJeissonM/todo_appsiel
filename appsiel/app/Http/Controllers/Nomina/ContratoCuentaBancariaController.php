<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Compras\ProveedorCuentaBancaria;
use App\Nomina\NomContrato;
use Illuminate\Support\Facades\Auth;

class ContratoCuentaBancariaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, $contrato_id)
    {
        $contrato = $this->obtener_contrato($contrato_id);
        $datos = $this->validar_datos($request);
        $datos['tercero_id'] = $contrato->core_tercero_id;

        ProveedorCuentaBancaria::create($datos);

        return redirect($this->url_redireccion($request, $contrato->id))
            ->with('flash_message', 'Cuenta bancaria CREADA correctamente.');
    }

    public function update(Request $request, $contrato_id, $cuenta_id)
    {
        $contrato = $this->obtener_contrato($contrato_id);
        $cuenta = $this->cuenta_del_contrato($contrato, $cuenta_id);

        $cuenta->fill($this->validar_datos($request));
        $cuenta->save();

        return redirect($this->url_redireccion($request, $contrato->id))
            ->with('flash_message', 'Cuenta bancaria MODIFICADA correctamente.');
    }

    public function destroy(Request $request, $contrato_id, $cuenta_id)
    {
        $contrato = $this->obtener_contrato($contrato_id);
        $this->cuenta_del_contrato($contrato, $cuenta_id)->delete();

        return redirect($this->url_redireccion($request, $contrato->id))
            ->with('flash_message', 'Cuenta bancaria ELIMINADA correctamente.');
    }

    protected function cuenta_del_contrato(NomContrato $contrato, $cuenta_id)
    {
        return ProveedorCuentaBancaria::where('tercero_id', $contrato->core_tercero_id)
            ->where('id', $cuenta_id)
            ->firstOrFail();
    }

    protected function obtener_contrato($contrato_id)
    {
        $contrato = NomContrato::with('tercero')->findOrFail($contrato_id);

        if (is_null($contrato->tercero) ||
            (int) $contrato->tercero->core_empresa_id !== (int) Auth::user()->empresa_id) {
            abort(403, 'El contrato no pertenece a la empresa actual.');
        }

        return $contrato;
    }

    protected function validar_datos(Request $request)
    {
        $this->validate($request, [
            'entidad_financiera_id' => 'required|exists:teso_entidades_financieras,id',
            'tipo_cuenta' => 'required|in:Ahorros,Corriente',
            'numero_cuenta' => 'required|max:80',
            'codigo_ciudad' => 'required|exists:core_ciudades,id',
            'estado' => 'required|in:Activo,Inactivo'
        ]);

        return $request->only(
            'entidad_financiera_id',
            'tipo_cuenta',
            'numero_cuenta',
            'codigo_ciudad',
            'estado'
        );
    }

    protected function url_redireccion(Request $request, $contrato_id)
    {
        return 'web/' . $contrato_id .
            '?id=' . $request->get('id') .
            '&id_modelo=' . $request->get('id_modelo') .
            '&id_transaccion=' . $request->get('id_transaccion') .
            '#cuentas-bancarias';
    }
}
