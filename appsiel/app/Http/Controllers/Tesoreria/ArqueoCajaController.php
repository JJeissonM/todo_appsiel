<?php

namespace App\Http\Controllers\Tesoreria;

use App\Core\Empresa;
use App\Core\Services\TurnoManager;
use App\Core\TurnoOperativo;
use App\Hotel\Support\HotelCreatorLabel;
use App\Sistema\Html\Boton;
use App\Sistema\TipoTransaccion;
use App\Tesoreria\ArqueoCaja;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMovimiento;
use App\User;
use App\VentasPos\Pdv;
use App\VentasPos\Services\CashRegisterShiftService;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;

class ArqueoCajaController extends ModeloController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function create()
    {
        return redirect('web/create?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&vista=tesoreria.arqueo_caja.create');
    }

    public function get_turnos_pdv_fecha()
    {
        $pdv = Pdv::where('id', (int)Input::get('pdv_id'))
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->first();

        if (is_null($pdv)) {
            return response()->json([
                'status' => 'error',
                'message' => 'El punto de venta no existe o no pertenece a la empresa.',
                'shifts' => []
            ], 404);
        }


        $turnoManager = app(TurnoManager::class);
        if ($turnoManager->enabledForPdv(Auth::user()->empresa_id, $pdv->id, 'tesoreria')) {
            $turnos = TurnoOperativo::where('core_empresa_id', Auth::user()->empresa_id)
                ->where('contexto_tipo', 'pdv')
                ->where('contexto_id', $pdv->id)
                ->where('fecha_operativa', Input::get('fecha'))
                ->orderBy('abierto_en')
                ->get()
                ->map(function ($turno) {
                    return array(
                        'id' => $turno->id,
                        'code' => $turno->codigo,
                        'state' => $turno->estado,
                        'opening_at' => is_null($turno->abierto_en) ? null : $turno->abierto_en->format('Y-m-d H:i:s'),
                        'closing_at' => is_null($turno->cerrado_en) ? null : $turno->cerrado_en->format('Y-m-d H:i:s'),
                        'cash_base' => (float)$turno->saldo_inicial,
                    );
                })->values();

            return response()->json(array(
                'status' => 'success',
                'mode' => 'TURNOS',
                'pdv_description' => $pdv->descripcion,
                'shifts' => $turnos,
                'range' => $turnos->count() === 1 ? $turnos->first() : null,
                'message' => $turnos->isEmpty()
                    ? 'No existen turnos operativos para la fecha seleccionada.'
                    : ($turnos->count() === 1 ? 'Se seleccionó el único turno operativo de la fecha.' : 'Seleccione el turno operativo que se va a arquear.')
            ));
        }

        $range = (new CashRegisterShiftService())->getDayRange($pdv, Input::get('fecha'));

        $message = '';
        if (is_null($range) || !$range['has_opening']) {
            $message = 'No hay apertura registrada para esta fecha. Puede ingresar manualmente el rango o dejar ambos campos vacíos para consultar el día completo.';
        } elseif (!$range['has_closing']) {
            $message = 'No hay cierre registrado para esta fecha. Ingrese manualmente la fecha y hora de cierre.';
        }

        return response()->json([
            'status' => 'success',
            'mode' => 'TRADICIONAL',
            'pdv_description' => $pdv->descripcion,
            'range' => $range,
            'shifts' => [],
            'message' => $message
        ]);
    }

    public function recalcular_saldo_inicial(Request $request)
    {
        $user = Auth::user();
        if (!ArqueoCaja::usuario_puede_recalcular_saldo_inicial($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tiene permiso para recalcular el saldo inicial.'
            ], 403);
        }

        $fecha = trim((string)$request->input('fecha'));
        $teso_caja_id = (int)$request->input('teso_caja_id');
        $fecha_hora_apertura = trim(str_replace('T', ' ', (string)$request->input('fecha_hora_apertura')));

        $fecha_valida = \DateTime::createFromFormat('Y-m-d', $fecha);
        if ($fecha_valida === false || $fecha_valida->format('Y-m-d') !== $fecha) {
            return response()->json([
                'status' => 'error',
                'message' => 'La fecha del arqueo no es válida.'
            ], 422);
        }

        $caja = TesoCaja::where('id', $teso_caja_id)
            ->where('core_empresa_id', $user->empresa_id)
            ->first();

        if (is_null($caja)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La caja seleccionada no existe o no pertenece a la empresa.'
            ], 422);
        }

        $pdvId = (int)$request->input('pdv_id');
        $turnoManager = app(TurnoManager::class);
        if ($pdvId > 0 && $turnoManager->enabledForPdv($user->empresa_id, $pdvId, 'tesoreria')) {
            $turno = TurnoOperativo::where('id', (int)$request->input('turno_operativo_id'))
                ->where('core_empresa_id', $user->empresa_id)
                ->where('contexto_tipo', 'pdv')
                ->where('contexto_id', $pdvId)
                ->first();
            if (is_null($turno)) {
                return response()->json(array('status' => 'error', 'message' => 'Seleccione un turno operativo válido antes de recalcular el saldo inicial.'), 422);
            }
            return response()->json(array(
                'status' => 'success',
                'saldo_inicial' => (float)$turno->saldo_inicial,
                'message' => 'Saldo inicial tomado del turno operativo seleccionado.'
            ));
        }

        if (!TesoMovimiento::usarMovimientosTesoreriaPorHora()) {
            $fecha_hora_apertura = null;
        } elseif ($fecha_hora_apertura !== '') {
            $formato_apertura = strlen($fecha_hora_apertura) === 16 ? 'Y-m-d H:i' : 'Y-m-d H:i:s';
            $apertura_valida = \DateTime::createFromFormat($formato_apertura, $fecha_hora_apertura);

            if ($apertura_valida === false
                || $apertura_valida->format($formato_apertura) !== $fecha_hora_apertura
                || $apertura_valida->format('Y-m-d') !== $fecha) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La fecha y hora de apertura no es válida o no pertenece al día del arqueo.'
                ], 422);
            }

            $fecha_hora_apertura = $apertura_valida->format('Y-m-d H:i:s');
        } else {
            $fecha_hora_apertura = null;
        }

        $saldo_inicial = TesoMovimiento::calcularSaldoInicialArqueo(
            $user->empresa_id,
            $caja->id,
            $fecha,
            $fecha_hora_apertura
        );

        return response()->json([
            'status' => 'success',
            'saldo_inicial' => $saldo_inicial,
            'message' => is_null($fecha_hora_apertura)
                ? 'Saldo calculado con los movimientos anteriores al día del arqueo.'
                : 'Saldo calculado con los movimientos anteriores a la apertura del arqueo.'
        ]);
    }

    // Generar vista para SHOW o IMPRIMIR
    public function vista_preliminar($id,$formato_impresion)
    {
        $registro = ArqueoCaja::find($id);
        $this->validar_acceso_registro($registro);

        $empresa = Empresa::find($registro->core_empresa_id);
        $doc_encabezado = [ 'documento' => 'ACTA DE ARQUEO DE CAJA', 'fecha' => $registro->fecha, 'titulo' => 'ARQUEO DE CAJA No. ' . $registro->id ];
        $user = User::where('email', $registro->creado_por)->first();
        $responsable = $this->get_responsable_label($registro, $user);
        $registro->billetes_contados = json_decode($registro->billetes_contados);
        $registro->monedas_contadas = json_decode($registro->monedas_contadas);
        $registro->detalles_mov_entradas = json_decode($registro->detalles_mov_entradas);
        $registro->detalles_mov_salidas = json_decode($registro->detalles_mov_salidas);

        if ( is_null( $registro->billetes_contados ) )
        {
            $registro->billetes_contados = [];
        }

        if ( is_null( $registro->monedas_contadas ) )
        {
            $registro->monedas_contadas = [];
        }

        if ( $registro->detalles_mov_entradas == 0 )
        {
            $registro->detalles_mov_entradas = [];
        }

        if ( $registro->detalles_mov_salidas == 0 )
        {
            $registro->detalles_mov_salidas = [];
        }

        $registro = $this->recalcular_movimientos_sistema_si_estan_vacios($registro);
        
        // Crear vista
        $view = View::make('tesoreria.arqueo_caja.formatos_impresion.' . $formato_impresion, compact('registro', 'empresa', 'doc_encabezado', 'user', 'responsable'))->render();

        return $view;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $registro = ArqueoCaja::find($id);
        $this->validar_acceso_registro($registro);

        $empresa = Empresa::find($registro->core_empresa_id);
        $doc_encabezado = [
            'documento'=>'ARQUEO DE CAJA No. ' . $registro->id,
            'fecha'=>$registro->fecha,
            'titulo'=>'ARQUEO DE CAJA'
        ];
        
        $user = User::where('email', $registro->creado_por)->first();
        $responsable = $this->get_responsable_label($registro, $user);
        $reg_anterior = app($this->modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($this->modelo->name_space)->where('id', '>', $registro->id)->min('id');
        $miga_pan = $this->get_miga_pan($this->modelo, 'Ver');
        $url_crear = '';
        $url_edit = '';

        $id_transaccion = TipoTransaccion::where('core_modelo_id', (int)Input::get('id_modelo'))->value('id');

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        if ($this->modelo->url_crear != '') {
            $url_crear = $this->modelo->url_crear . $variables_url;
        }
        if ($this->modelo->url_edit != '') {
            $url_edit = $this->modelo->url_edit . $variables_url;
        }
        // ENLACES
        $botones = [];
        if ($this->modelo->enlaces != '') {
            $enlaces = json_decode($this->modelo->enlaces);
            $i = 0;
            foreach ($enlaces as $fila) {
                $botones[$i] = new Boton($fila);
            }
        }
        $registro->billetes_contados = json_decode($registro->billetes_contados);
        $registro->monedas_contadas = json_decode($registro->monedas_contadas);
        $registro->detalles_mov_entradas = json_decode($registro->detalles_mov_entradas);
        $registro->detalles_mov_salidas = json_decode($registro->detalles_mov_salidas);

        if ( is_null( $registro->billetes_contados ) )
        {
            $registro->billetes_contados = [];
        }

        if ( is_null( $registro->monedas_contadas ) )
        {
            $registro->monedas_contadas = [];
        }

        if ( $registro->detalles_mov_entradas == 0 )
        {
            $registro->detalles_mov_entradas = [];
        }

        if ( $registro->detalles_mov_salidas == 0 )
        {
            $registro->detalles_mov_salidas = [];
        }

        $registro = $this->recalcular_movimientos_sistema_si_estan_vacios($registro);

        return view('tesoreria.arqueo_caja.show', compact('miga_pan', 'registro', 'url_crear', 'url_edit', 'reg_anterior', 'reg_siguiente', 'botones', 'empresa', 'doc_encabezado', 'user', 'responsable'));
    }

    protected function get_responsable_label($registro, $user)
    {
        $fecha_hora = !empty($registro->fecha_hora_apertura)
            ? $registro->fecha_hora_apertura
            : $registro->created_at;

        return HotelCreatorLabel::userLabel($user ?: $registro->creado_por, $fecha_hora, $registro->pdv_id);
    }

    /**
     * 
     */
    public function imprimir($id)
    {
        $view = ArqueoCajaController::vista_preliminar( $id, Input::get('formato_impresion_id') );  

        $orientacion = 'portrait';
        $tam_hoja = 'Letter';

        // Crear PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);
        
        return $pdf->stream('arqueocaja.pdf');//stream(); 
        
        /*echo $view;*/
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $registro = ArqueoCaja::find($id);
        $this->validar_acceso_registro($registro);

        $miga_pan = $this->get_miga_pan($this->modelo, 'Editar');
        $lista_campos = $this->get_campos_modelo($this->modelo, $registro, 'edit');
        $form_create = [
            'url' => $this->modelo->url_form_create,
            'campos' => $lista_campos
        ];
        $archivo_js = app($this->modelo->name_space)->archivo_js;
        $url_action = 'web/' . $id;

        $registro->billetes_contados = json_decode($registro->billetes_contados);
        $registro->monedas_contadas = json_decode($registro->monedas_contadas);
        
        return view('tesoreria.arqueo_caja.edit', compact('form_create', 'miga_pan', 'registro', 'archivo_js', 'url_action'));
    }

    protected function recalcular_movimientos_sistema_si_estan_vacios($registro)
    {
        $sin_detalle_entradas = empty((array)$registro->detalles_mov_entradas);
        $sin_detalle_salidas = empty((array)$registro->detalles_mov_salidas);

        if ( (float)$registro->total_mov_entradas != 0 || (float)$registro->total_mov_salidas != 0 || !$sin_detalle_entradas || !$sin_detalle_salidas ) {
            return $registro;
        }

        $modelo = new ArqueoCaja();
        $datos = $modelo->get_datos_adicionales([
            'fecha' => $registro->fecha,
            'teso_caja_id' => $registro->teso_caja_id,
            'base' => (float)$registro->base,
            'total_billetes' => (float)$registro->total_billetes,
            'total_monedas' => (float)$registro->total_monedas,
            'otros_saldos' => (float)$registro->otros_saldos,
            'creado_por' => $registro->creado_por,
            'pdv_id' => isset($registro->pdv_id) ? $registro->pdv_id : 0,
            'fecha_hora_apertura' => isset($registro->fecha_hora_apertura) ? $registro->fecha_hora_apertura : null,
            'fecha_hora_cierre' => isset($registro->fecha_hora_cierre) ? $registro->fecha_hora_cierre : null,
            'turno_operativo_id' => isset($registro->turno_operativo_id) ? $registro->turno_operativo_id : null,
            'sumar_efectivo_base_en_saldo_esperado' => (int)config('ventas_pos.sumar_efectivo_base_en_saldo_esperado')
        ]);

        $registro->detalles_mov_entradas = json_decode($datos['movimientos_entradas']) ?: [];
        $registro->total_mov_entradas = $datos['total_mov_entradas'];
        $registro->detalles_mov_salidas = json_decode($datos['movimientos_salidas']) ?: [];
        $registro->total_mov_salidas = $datos['total_mov_salidas'];
        $registro->lbl_total_sistema = $datos['lbl_total_sistema'];
        $registro->total_saldo = $datos['total_saldo'];

        return $registro;
    }

    protected function validar_acceso_registro($registro)
    {
        if (is_null($registro)) {
            abort(404);
        }

        $user = Auth::user();
        $roles_sin_filtro = config('filtrado_registros.roles_sin_filtro', []);

        if (is_null($user) || empty($user->email)) {
            abort(403);
        }

        foreach ($user->roles as $role) {
            if (in_array($role->name, $roles_sin_filtro)) {
                return;
            }
        }

        if ($registro->creado_por != $user->email) {
            abort(403, 'No tiene permiso para consultar este arqueo de caja.');
        }
    }

}
