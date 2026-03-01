<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Compras\Proveedor;
use App\Compras\ProveedorCuentaBancaria;

class ProveedorCuentaBancariaController extends Controller
{
    public function store(Request $request, $proveedor_id)
    {
        $proveedor = Proveedor::findOrFail($proveedor_id);

        $datos = $this->validar_datos($request);
        $datos['tercero_id'] = $proveedor->core_tercero_id;

        ProveedorCuentaBancaria::create($datos);

        return redirect($this->url_redireccion($request, $proveedor->id))
            ->with('flash_message', 'Cuenta bancaria CREADA correctamente.');
    }

    public function update(Request $request, $proveedor_id, $cuenta_id)
    {
        $proveedor = Proveedor::findOrFail($proveedor_id);

        $cuenta = ProveedorCuentaBancaria::where('tercero_id', $proveedor->core_tercero_id)
            ->where('id', $cuenta_id)
            ->firstOrFail();

        $cuenta->fill($this->validar_datos($request));
        $cuenta->save();

        return redirect($this->url_redireccion($request, $proveedor->id))
            ->with('flash_message', 'Cuenta bancaria MODIFICADA correctamente.');
    }

    public function destroy(Request $request, $proveedor_id, $cuenta_id)
    {
        $proveedor = Proveedor::findOrFail($proveedor_id);

        ProveedorCuentaBancaria::where('tercero_id', $proveedor->core_tercero_id)
            ->where('id', $cuenta_id)
            ->firstOrFail()
            ->delete();

        return redirect($this->url_redireccion($request, $proveedor->id))
            ->with('flash_message', 'Cuenta bancaria ELIMINADA correctamente.');
    }

    protected function validar_datos(Request $request)
    {
        $this->validate(
            $request,
            [
                'entidad_financiera_id' => 'required|exists:teso_entidades_financieras,id',
                'tipo_cuenta' => 'required|in:Ahorros,Corriente',
                'numero_cuenta' => 'required|max:80',
                'codigo_ciudad' => 'required|exists:core_ciudades,id',
                'estado' => 'required|in:Activo,Inactivo'
            ]
        );

        return $request->only(
            'entidad_financiera_id',
            'tipo_cuenta',
            'numero_cuenta',
            'codigo_ciudad',
            'estado'
        );
    }

    protected function url_redireccion(Request $request, $proveedor_id)
    {
        return 'compras_proveedores/' . $proveedor_id .
            '?id=' . $request->get('id') .
            '&id_modelo=' . $request->get('id_modelo') .
            '&id_transaccion=' . $request->get('id_transaccion') .
            '#cuentas-bancarias';
    }
}
