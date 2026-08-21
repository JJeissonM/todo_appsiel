<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\ParametroLegal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class RegistrosDocumentosController extends TransaccionController
{

    /*
        Pre-formulario donde seleccionar documento y concepto
    */
    public function crear_registros1()
    {
        $opciones1 = NomDocEncabezado::where('estado','Activo')
                                    ->orderBy('descripcion')
                                    ->get();
        $vec1['']='';
        foreach ($opciones1 as $opcion){
            $vec1[$opcion->id] = $opcion->get_label_documento() . ' - ' . $opcion->descripcion;
        }
        $documentos = $vec1;

        $modo_liquidacion_id = 2; //2 = Manual
        $opciones2 = NomConcepto::where('estado','Activo')
                                ->where('modo_liquidacion_id', $modo_liquidacion_id)
                                ->orderBy('descripcion')
                                ->get();
        $vec2['']='';
        foreach ($opciones2 as $opcion){
            $vec2[$opcion->id] = $opcion->descripcion;
        }
        $conceptos = $vec2;


        $miga_pan = [
                        ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                        ['url'=>'web?id=17&id_modelo=91','etiqueta'=>'Registros documentos nómina'],
                        ['url'=>'NO','etiqueta'=>'Ingreso de registros: seleccionar filtros']
                    ];

        return view('nomina.create_registros1',compact('documentos','conceptos','miga_pan'));
    }

    public function filtros_registros($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find((int) $nom_doc_encabezado_id);
        if (is_null($documento) || !$documento->esta_activo_para_transacciones()) {
            return response()->json([
                'mensaje' => 'El documento de nómina no existe o no está activo.'
            ], 422);
        }

        $contratos = $documento->contratos_asignados_para_registros();
        $empleados = [];
        $grupos = [];
        $cargos = [];

        foreach ($contratos as $contrato) {
            if (is_null($contrato->tercero)) {
                continue;
            }

            $empleados[] = [
                'id' => (int) $contrato->id,
                'grupo_empleado_id' => (int) $contrato->grupo_empleado_id,
                'cargo_id' => (int) $contrato->cargo_id,
                'texto' => $contrato->tercero->numero_identificacion . ' - ' . $contrato->tercero->descripcion
            ];

            if (!is_null($contrato->grupo_empleado)) {
                $grupos[$contrato->grupo_empleado->id] = $contrato->grupo_empleado->descripcion;
            }
            if (!is_null($contrato->cargo)) {
                $cargos[$contrato->cargo->id] = $contrato->cargo->descripcion;
            }
        }

        asort($grupos);
        asort($cargos);

        return response()->json([
            'empleados' => $empleados,
            'grupos' => $this->opcionesJson($grupos),
            'cargos' => $this->opcionesJson($cargos)
        ]);
    }

    protected function opcionesJson(array $opciones)
    {
        $respuesta = [];
        foreach ($opciones as $id => $texto) {
            $respuesta[] = ['id' => (int) $id, 'texto' => $texto];
        }

        return $respuesta;
    }


    /*
        Formulario para registrar los valores a liquidar del concepto y el documento seleccionado
    */
    public function crear_registros2(Request $request)
    {
        $documento = NomDocEncabezado::find((int) $request->nom_doc_encabezado_id);
        $concepto = NomConcepto::find((int) $request->nom_concepto_id);

        if (is_null($documento) || !$documento->esta_activo_para_transacciones()) {
            return redirect()->back()->withInput()
                ->with('mensaje_error', 'Debe seleccionar un documento de nómina activo.');
        }

        if (is_null($concepto) || $concepto->estado !== 'Activo' || (int) $concepto->modo_liquidacion_id !== 2) {
            return redirect()->back()->withInput()
                ->with('mensaje_error', 'Debe seleccionar un concepto manual activo.');
        }

        $grupoEmpleadoId = $this->filtroEntero($request->grupo_empleado_id);
        $cargoId = $this->filtroEntero($request->cargo_id);
        $contratoId = $this->filtroEntero($request->nom_contrato_id);
        $empleados = $documento->contratos_asignados_para_registros($grupoEmpleadoId, $cargoId, $contratoId);

        if ($empleados->isEmpty()) {
            return redirect()->back()->withInput()
                ->with('mensaje_error', 'No hay empleados del documento que cumplan los filtros seleccionados.');
        }

        $contratoIds = $empleados->pluck('id')->toArray();
        $cant_registros = NomDocRegistro::where([
                'nom_doc_encabezado_id' => $documento->id,
                'nom_concepto_id' => $concepto->id
            ])
            ->whereIn('nom_contrato_id', $contratoIds)
            ->count();
        
        $id_app = Input::get('id');

        $miga_pan = [
                        ['url'=>'nomina?id='.$id_app,'etiqueta'=>'Nómina'],
                        ['url'=>'web?id=17&id_modelo=91','etiqueta'=>'Registros documentos nómina'],
                        ['url'=>'nomina/crear_registros?id=17&id_modelo=91','etiqueta'=>'Ingreso de registros: seleccionar filtros'],
                        ['url'=>'NO','etiqueta'=>'Ingresar datos']
                    ];
         
        // Si ya tienen al menos un empleado con concepto ingresado
        $datosVista = self::get_array_tabla_registros(
            $concepto->id,
            $documento->id,
            $request->ruta,
            $grupoEmpleadoId,
            $cargoId,
            $contratoId
        );

        if ($cant_registros > 0) {
            return view('nomina.editar_registros1', array_merge($datosVista, ['miga_pan' => $miga_pan]));
        }

        return view('nomina.create_registros2', array_merge($datosVista, ['miga_pan' => $miga_pan]));
    }

    protected function filtroEntero($valor)
    {
        return is_numeric($valor) && (int) $valor > 0 ? (int) $valor : null;
    }

    public static function get_array_tabla_registros($nom_concepto_id, $nom_doc_encabezado_id, $ruta, $grupoEmpleadoId = null, $cargoId = null, $contratoId = null)
    {
        // Se obtienen las descripciones del concepto y documento de nómina
        $concepto = NomConcepto::find( $nom_concepto_id );
        $documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

        // Solo se incluyen contratos asignados al documento y que cumplan los filtros.
        $empleados = $documento->contratos_asignados_para_registros($grupoEmpleadoId, $cargoId, $contratoId);
        $contratoIds = $empleados->pluck('id')->toArray();
        
        // Verificar si ya se han ingresado registro para ese concepto y documento
        $cant_registros = NomDocRegistro::where([
                                                'nom_doc_encabezado_id'=>$nom_doc_encabezado_id,
                                                'nom_concepto_id'=>$nom_concepto_id
                                            ])
                                            ->whereIn('nom_contrato_id', $contratoIds)
                                            ->count();

        $filtros = [
            'grupo_empleado_id' => $grupoEmpleadoId,
            'cargo_id' => $cargoId,
            'nom_contrato_id' => $contratoId,
            'grupo' => '',
            'cargo' => '',
            'empleado' => ''
        ];
        $primerEmpleado = $empleados->first();
        if (!is_null($primerEmpleado)) {
            if (!is_null($grupoEmpleadoId) && !is_null($primerEmpleado->grupo_empleado)) {
                $filtros['grupo'] = $primerEmpleado->grupo_empleado->descripcion;
            }
            if (!is_null($cargoId) && !is_null($primerEmpleado->cargo)) {
                $filtros['cargo'] = $primerEmpleado->cargo->descripcion;
            }
            if (!is_null($contratoId) && !is_null($primerEmpleado->tercero)) {
                $filtros['empleado'] = $primerEmpleado->tercero->descripcion;
            }
        }

        // Si ya tienen al menos un empleado con concepto ingresado
        if( $cant_registros > 0 )
        {
            
            // Se crea un vector con los valores de los conceptos para modificarlas
            $vec_registros = array();
            $i=0;
            foreach($empleados as $empleado)
            {
                $vec_empleados[$i]['core_tercero_id'] = $empleado->tercero->id;
                $vec_empleados[$i]['nombre'] = $empleado->tercero->descripcion;
                
                // Se verifica si cada persona tiene valor ingresado
                $vec_empleados[$i]['nom_contrato_id'] = $empleado->id;

                $datos = NomDocRegistro::where(['nom_doc_encabezado_id'=>$nom_doc_encabezado_id,
                                                'nom_concepto_id'=>$nom_concepto_id,
                                                'nom_contrato_id'=>$empleado->id])
                                        ->get()
                                        ->first();

                $vec_empleados[$i]['valor_concepto'] = 0;
                $vec_empleados[$i]['cantidad_horas'] = 0;
                $vec_empleados[$i]['nom_registro_id'] = "no";
                
                // Si el persona tiene calificacion se envian los datos de esta para editar
                if( !is_null($datos) )
                {
                    switch ($concepto->naturaleza)
                    {
                        case 'devengo':
                            $vec_empleados[$i]['valor_concepto'] = $datos->valor_devengo;
                            break;
                        case 'deduccion':
                            $vec_empleados[$i]['valor_concepto'] = $datos->valor_deduccion;
                            break;
                        
                        default:
                            # code...
                            break;
                    }

                    if ( (float)$concepto->porcentaje_sobre_basico != 0 )
                    {
                        $vec_empleados[$i]['cantidad_horas'] = $datos->cantidad_horas;
                    }

                    $vec_empleados[$i]['nom_registro_id'] = $datos->id;

                }
                
                $i++;
            } // Fin foreach (llenado de array con datos)
            return ['vec_empleados'=>$vec_empleados,
                'cantidad_empleados'=>count($empleados),
                'concepto'=>$concepto,
                'documento'=>$documento,
                'ruta'=>$ruta,
                'filtros'=>$filtros];
        }else{
            // Si no tienen datos, se crean por primera vez
            return ['empleados'=>$empleados,
                'cantidad_empleados'=>count($empleados),
                'concepto'=>$concepto,
                'documento'=>$documento,
                'ruta'=>$ruta,
                'filtros'=>$filtros];
        }
    }

    /**
     * Para almacenar los registros de documentos
     *  Normalmente para conceptos tipo Manuales
     */
    public function store(Request $request)
    {
        try {
            $contexto = $this->validarContextoGuardado($request);
        } catch (\InvalidArgumentException $e) {
            return $this->redirectListado($request)->with('mensaje_error', $e->getMessage());
        }

        $usuario = Auth::user();
        $documento = $contexto->documento;
        $concepto = $contexto->concepto;
        $datos = [
            'nom_doc_encabezado_id' => $documento->id,
            'fecha' => $documento->fecha,
            'core_empresa_id' => $documento->core_empresa_id,
            'nom_concepto_id' => $concepto->id,
            'estado' => 'Activo',
            'creado_por' => $usuario->email,
            'modificado_por' => ''
        ];

        DB::beginTransaction();
        try {
            foreach ($contexto->contratos as $i => $contrato) {
                if (NomDocRegistro::where([
                    'nom_doc_encabezado_id' => $documento->id,
                    'nom_contrato_id' => $contrato->id,
                    'nom_concepto_id' => $concepto->id
                ])->exists()) {
                    throw new \InvalidArgumentException('Ya existe un registro para ' . $contrato->tercero->descripcion . ' con el concepto seleccionado.');
                }

                if ($request->has('valor')) {
                    $this->registrar_por_valor($concepto, $contrato, $datos, $this->valorNumerico($request->input('valor.' . $i)));
                }

                if ($request->has('cantidad_horas')) {
                    $this->registrar_por_cantidad_horas($concepto, $contrato, $datos, $this->valorNumerico($request->input('cantidad_horas.' . $i)));
                }
            }

            $this->actualizar_totales_documento($documento->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $mensaje = $e instanceof \InvalidArgumentException
                ? $e->getMessage()
                : 'No fue posible guardar los registros de nómina.';
            return $this->redirectListado($request)->with('mensaje_error', $mensaje);
        }

        return $this->redirectListado($request)->with('flash_message', 'Registros CREADOS correctamente. Nómina: ' . $documento->descripcion . ', Concepto: ' . $concepto->descripcion);
    }

    public function registrar_por_valor($concepto, $contrato, $datos, $valor)
    {
        if ($valor > 0) {
            $valores = $this->get_valor_devengo_deduccion($concepto->naturaleza, $valor);

            NomDocRegistro::create(
                                    $datos +
                                    [ 'core_tercero_id' => $contrato->core_tercero_id ] +
                                    [ 'nom_contrato_id' => $contrato->id ] +
                                    [ 'valor_devengo' => round( $valores[0], 0) ] + 
                                    [ 'valor_deduccion' => round( $valores[1], 0) ]
                                );
        }
    }

    public function registrar_por_cantidad_horas($concepto, $contrato, $datos, $cantidad_horas)
    {
        if ($cantidad_horas > 0) {
            $salario_x_hora = $contrato->sueldo / ParametroLegal::horas_laborales_para_fecha($datos['fecha']);

            $valor_a_liquidar = $concepto->get_valor_hora_porcentaje_sobre_basico($salario_x_hora, $cantidad_horas);           

            $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor_a_liquidar );

            NomDocRegistro::create(
                                    $datos +
                                    [ 'core_tercero_id' => $contrato->core_tercero_id ] +
                                    [ 'nom_contrato_id' => $contrato->id ] +
                                    [ 'valor_devengo' => round( $valores[0], 0) ] + 
                                    [ 'valor_deduccion' => round( $valores[1], 0) ] + 
                                    [ 'cantidad_horas' => $cantidad_horas ]
                                );
        }

    }

    protected function validarContextoGuardado(Request $request)
    {
        $documento = NomDocEncabezado::find((int) $request->nom_doc_encabezado_id);
        $concepto = NomConcepto::find((int) $request->nom_concepto_id);

        if (is_null($documento) || !$documento->esta_activo_para_transacciones()) {
            throw new \InvalidArgumentException('El documento de nómina no existe o no está activo.');
        }

        if (is_null($concepto) || $concepto->estado !== 'Activo' || (int) $concepto->modo_liquidacion_id !== 2) {
            throw new \InvalidArgumentException('El concepto no existe, no está activo o no es de liquidación Manual.');
        }

        $grupoEmpleadoId = $this->filtroEntero($request->grupo_empleado_id);
        $cargoId = $this->filtroEntero($request->cargo_id);
        $filtroContratoId = $this->filtroEntero($request->filtro_nom_contrato_id);
        $permitidos = $documento->contratos_asignados_para_registros($grupoEmpleadoId, $cargoId, $filtroContratoId);
        $permitidosPorId = [];
        foreach ($permitidos as $contrato) {
            $permitidosPorId[(int) $contrato->id] = $contrato;
        }

        $contratoIds = $request->input('nom_contrato_id', []);
        $terceroIds = $request->input('core_tercero_id', []);
        if (!is_array($contratoIds) || empty($contratoIds) || !is_array($terceroIds)) {
            throw new \InvalidArgumentException('No se recibieron empleados para guardar.');
        }

        $contratos = [];
        $contratosResueltos = [];
        foreach ($contratoIds as $indice => $contratoId) {
            $contratoId = (int) $contratoId;
            $terceroId = (int) (isset($terceroIds[$indice]) ? $terceroIds[$indice] : 0);
            $contrato = isset($permitidosPorId[$contratoId]) ? $permitidosPorId[$contratoId] : null;

            // Si el ID oculto quedó desfasado, se resuelve nuevamente usando
            // el tercero y los filtros confirmados por el servidor.
            if (is_null($contrato) || (int) $contrato->core_tercero_id !== $terceroId) {
                $coincidencias = $permitidos->filter(function ($contratoPermitido) use ($terceroId) {
                    return (int) $contratoPermitido->core_tercero_id === $terceroId;
                })->values();

                if ($coincidencias->count() !== 1) {
                    throw new \InvalidArgumentException('No fue posible determinar un contrato único para uno de los empleados según los filtros seleccionados.');
                }
                $contrato = $coincidencias->first();
            }

            if (isset($contratosResueltos[$contrato->id])) {
                throw new \InvalidArgumentException('El mismo contrato fue enviado más de una vez.');
            }

            $contratosResueltos[$contrato->id] = true;
            $contratos[] = $contrato;
        }

        if (!$request->has('valor') && !$request->has('cantidad_horas')) {
            throw new \InvalidArgumentException('No se recibió el valor ni la cantidad de horas del concepto.');
        }

        return (object) compact('documento', 'concepto', 'contratos');
    }

    protected function valorNumerico($valor)
    {
        if (is_null($valor) || trim((string) $valor) === '') {
            return 0;
        }

        if (!is_numeric($valor) || (float) $valor < 0) {
            throw new \InvalidArgumentException('Los valores y las cantidades de horas deben ser números mayores o iguales a cero.');
        }

        return (float) $valor;
    }

    protected function redirectListado(Request $request)
    {
        return redirect('web?id=' . (int) $request->app_id . '&id_modelo=' . (int) $request->modelo_id);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        switch($id){
            case 'editar1':
                try {
                    $contexto = $this->validarContextoGuardado($request);
                } catch (\InvalidArgumentException $e) {
                    return $this->redirectListado($request)->with('mensaje_error', $e->getMessage());
                }

                $usuario = Auth::user();
                $documento = $contexto->documento;
                $concepto = $contexto->concepto;
                $datos = [
                    'nom_doc_encabezado_id' => $documento->id,
                    'fecha' => $documento->fecha,
                    'core_empresa_id' => $documento->core_empresa_id,
                    'nom_concepto_id' => $concepto->id,
                    'estado' => 'Activo',
                    'creado_por' => $usuario->email,
                    'modificado_por' => ''
                ];

                DB::beginTransaction();
                try {
                    foreach ($contexto->contratos as $i => $contrato) {
                        $registroId = $request->input('nom_registro_id.' . $i);
                        if ($registroId === 'no') {
                            if ($request->has('valor')) {
                                $this->registrar_por_valor($concepto, $contrato, $datos, $this->valorNumerico($request->input('valor.' . $i)));
                            }
                            if ($request->has('cantidad_horas')) {
                                $this->registrar_por_cantidad_horas($concepto, $contrato, $datos, $this->valorNumerico($request->input('cantidad_horas.' . $i)));
                            }
                            continue;
                        }

                        $registro = NomDocRegistro::where([
                            'id' => (int) $registroId,
                            'nom_doc_encabezado_id' => $documento->id,
                            'nom_contrato_id' => $contrato->id,
                            'nom_concepto_id' => $concepto->id
                        ])->first();
                        if (is_null($registro)) {
                            throw new \InvalidArgumentException('Uno de los registros no corresponde al empleado y filtros seleccionados.');
                        }

                        if ($request->has('valor')) {
                            $this->actualizar_por_valor($registro, $concepto, $this->valorNumerico($request->input('valor.' . $i)), $usuario);
                        }
                        if ($request->has('cantidad_horas')) {
                            $this->actualizar_por_cantidad_horas($registro, $concepto, $contrato, $this->valorNumerico($request->input('cantidad_horas.' . $i)), $usuario);
                        }
                    }

                    $this->actualizar_totales_documento($documento->id);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $mensaje = $e instanceof \InvalidArgumentException
                        ? $e->getMessage()
                        : 'No fue posible actualizar los registros de nómina.';
                    return $this->redirectListado($request)->with('mensaje_error', $mensaje);
                }

                return $this->redirectListado($request)->with('flash_message', 'Registros ACTUALIZADOS correctamente. Nómina: ' . $documento->descripcion . ', Concepto: ' . $concepto->descripcion);

            default:
                // code
            break;

        }
    }

    public function actualizar_por_valor( $registro, $concepto, $valor, $usuario )
    {
        $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor );

        if ( $valor == 0 )
        {
            // Eliminar el registro
            $registro->delete();
        }else{
            $registro->fill( 
                            [ 'valor_devengo' => $valores[0] ] + 
                            [ 'valor_deduccion' => $valores[1] ] + 
                            [ 'modificado_por' => $usuario->email] );
            $registro->save();
        }
    }

    public function actualizar_por_cantidad_horas($registro, $concepto, $contrato, $cantidad_horas, $usuario)
    {
        if ( $cantidad_horas == 0 )
        {
            // Eliminar el registro
            $registro->delete();
        }else{

            $salario_x_hora = $contrato->sueldo / ParametroLegal::horas_laborales_para_fecha($registro->fecha);

            $valor_a_liquidar = $concepto->get_valor_hora_porcentaje_sobre_basico($salario_x_hora, $cantidad_horas);

            $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor_a_liquidar );

            $registro->fill( 
                            [ 'valor_devengo' => $valores[0] ] + 
                            [ 'valor_deduccion' => $valores[1] ] + 
                            [ 'cantidad_horas' => $cantidad_horas ]  + 
                            [ 'modificado_por' => $usuario->email ] );
            $registro->save();
        }
    }


    function actualizar_totales_documento($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);
        $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_devengo');
        $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_deduccion');
        $documento->save();
    }
    
    function get_valor_devengo_deduccion( $naturaleza, $valor )
    {
        $valor_devengo = 0;
        $valor_deduccion = 0;
        switch ($naturaleza) {
            case 'devengo':
                $valor_devengo = $valor;
                $valor_deduccion = 0;
                break;
            case 'deduccion':
                $valor_devengo = 0;
                $valor_deduccion = $valor;
                break;
            
            default:
                # code...
                break;
        }

        return [$valor_devengo, $valor_deduccion];
    }
}
