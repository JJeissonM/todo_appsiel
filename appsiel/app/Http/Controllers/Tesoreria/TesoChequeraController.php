<?php

namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;
use App\Sistema\Modelo;
use App\Tesoreria\Services\ChequeraService;
use App\Tesoreria\TesoChequera;
use App\Tesoreria\TesoCuentaBancaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class TesoChequeraController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new ChequeraService();
    }

    public function create($teso_cuenta_bancaria_id)
    {
        $cuenta = TesoCuentaBancaria::find($teso_cuenta_bancaria_id);
        if (is_null($cuenta)) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))
                ->with('mensaje_error', 'Cuenta bancaria no encontrada.');
        }

        return view('tesoreria.chequeras.create', compact('cuenta'));
    }

    public function store(Request $request, $teso_cuenta_bancaria_id)
    {
        $this->validar_formulario($request);

        $data = $request->all();
        $data['teso_cuenta_bancaria_id'] = (int)$teso_cuenta_bancaria_id;

        try {
            if ($this->service->existe_solapamiento($teso_cuenta_bancaria_id, $data['numero_inicial'], $data['numero_final'])) {
                return $this->redirect_show($teso_cuenta_bancaria_id, 'mensaje_error', 'Ya existe una chequera con rango solapado para esta cuenta.');
            }

            $this->service->create($data);
        } catch (\Exception $e) {
            return $this->redirect_show($teso_cuenta_bancaria_id, 'mensaje_error', $e->getMessage());
        }

        return $this->redirect_show($teso_cuenta_bancaria_id, 'flash_message', 'Chequera creada correctamente.');
    }

    public function edit($teso_cuenta_bancaria_id, $id)
    {
        $cuenta = TesoCuentaBancaria::find($teso_cuenta_bancaria_id);
        $chequera = TesoChequera::where('teso_cuenta_bancaria_id', $teso_cuenta_bancaria_id)
            ->where('id', $id)
            ->first();

        if (is_null($cuenta) || is_null($chequera)) {
            return $this->redirect_show($teso_cuenta_bancaria_id, 'mensaje_error', 'Chequera no encontrada.');
        }

        return view('tesoreria.chequeras.edit', compact('cuenta', 'chequera'));
    }

    public function update(Request $request, $teso_cuenta_bancaria_id, $id)
    {
        $this->validar_formulario($request);

        $chequera = TesoChequera::where('teso_cuenta_bancaria_id', $teso_cuenta_bancaria_id)
            ->where('id', $id)
            ->first();
        if (is_null($chequera)) {
            return $this->redirect_show($teso_cuenta_bancaria_id, 'mensaje_error', 'Chequera no encontrada.');
        }

        try {
            if ($this->service->existe_solapamiento($teso_cuenta_bancaria_id, $request->numero_inicial, $request->numero_final, $chequera->id)) {
                return $this->redirect_show($teso_cuenta_bancaria_id, 'mensaje_error', 'Ya existe una chequera con rango solapado para esta cuenta.');
            }

            $this->service->validar_rango($request->numero_inicial, $request->numero_final, $request->consecutivo_actual);
        } catch (\Exception $e) {
            return $this->redirect_show($teso_cuenta_bancaria_id, 'mensaje_error', $e->getMessage());
        }

        $chequera->fill($request->all());
        if ((int)$chequera->consecutivo_actual > (int)$chequera->numero_final) {
            $chequera->estado = 'Agotada';
        }
        $chequera->save();

        return $this->redirect_show($teso_cuenta_bancaria_id, 'flash_message', 'Chequera actualizada correctamente.');
    }

    public function destroy($teso_cuenta_bancaria_id, $id)
    {
        $chequera = TesoChequera::where('teso_cuenta_bancaria_id', $teso_cuenta_bancaria_id)
            ->where('id', $id)
            ->first();
        if (is_null($chequera)) {
            return $this->redirect_show($teso_cuenta_bancaria_id, 'mensaje_error', 'Chequera no encontrada.');
        }

        $chequera->delete();
        return $this->redirect_show($teso_cuenta_bancaria_id, 'flash_message', 'Chequera eliminada correctamente.');
    }

    public function get_consecutivo($teso_cuenta_bancaria_id)
    {
        try {
            $consecutivo = $this->service->get_consecutivo((int)$teso_cuenta_bancaria_id);
            return response()->json(['status' => 'ok', 'consecutivo' => $consecutivo], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function actualizar_consecutivo(Request $request, $teso_cuenta_bancaria_id, $id)
    {
        try {
            $numero = $request->numero_cheque;
            if (!is_null($numero)) {
                $this->service->actualizar_consecutivo_por_numero((int)$teso_cuenta_bancaria_id, (int)$numero);
            } else {
                $this->service->actualizar_consecutivo((int)$id);
            }

            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    protected function validar_formulario(Request $request)
    {
        $this->validate($request, [
            'descripcion' => 'required',
            'numero_inicial' => 'required|integer|min:1',
            'numero_final' => 'required|integer|min:1',
            'consecutivo_actual' => 'required|integer|min:1',
            'estado' => 'required'
        ]);
    }

    protected function redirect_show($teso_cuenta_bancaria_id, $tipo_mensaje, $mensaje)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));
        $url_show = 'web/' . $teso_cuenta_bancaria_id;
        if (!is_null($modelo) && $modelo->id == 33) {
            $url_show = 'teso_cuentas_bancarias/' . $teso_cuenta_bancaria_id;
        }

        return redirect($url_show . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))
            ->with($tipo_mensaje, $mensaje);
    }
}
