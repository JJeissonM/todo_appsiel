<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\ParametroLiquidacionPrestacionesSociales;
use App\Nomina\LibroVacacion;
use App\Nomina\Services\CalculoFechaVacaciones;
use Illuminate\Support\Facades\DB;

class ProgramacionVacacion extends Model
{
    protected $table = 'nom_novedades_tnl';

    protected $fillable = ['nom_concepto_id', 'nom_contrato_id', 'fecha_inicial_tnl', 'fecha_final_tnl', 'cantidad_dias_tnl', 'cantidad_horas_tnl', 'tipo_novedad_tnl', 'codigo_diagnostico_incapacidad', 'numero_incapacidad', 'fecha_expedicion_incapacidad', 'origen_incapacidad', 'clase_incapacidad', 'fecha_incapacidad', 'valor_a_pagar_eps', 'valor_a_pagar_arl', 'valor_a_pagar_afp', 'valor_a_pagar_empresa', 'observaciones', 'estado', 'cantidad_dias_amortizados', 'cantidad_dias_pendientes_amortizar', 'es_prorroga', 'novedad_tnl_anterior_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empleado', 'Inicio',  'Fin', 'Cant. días', 'Días amortizados', 'Observaciones', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public $archivo_js = 'assets/js/nomina/programacion_vacaciones.js?v=20261105_2';

    public function concepto()
    {
        return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
    }

    public function contrato()
    {
        return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return ProgramacionVacacion::leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_novedades_tnl.nom_concepto_id')
            ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_novedades_tnl.nom_contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->where([['nom_novedades_tnl.tipo_novedad_tnl', '=', 'vacaciones']])
            ->select(
                'core_terceros.descripcion AS campo1',
                'nom_novedades_tnl.fecha_inicial_tnl AS campo2',
                'nom_novedades_tnl.fecha_final_tnl AS campo3',
                'nom_novedades_tnl.cantidad_dias_tnl AS campo4',
                'nom_novedades_tnl.cantidad_dias_amortizados AS campo5',
                'nom_novedades_tnl.observaciones AS campo6',
                'nom_novedades_tnl.estado AS campo7',
                'nom_novedades_tnl.id AS campo8'
            )
			->orderBy('nom_novedades_tnl.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ProgramacionVacacion::leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_novedades_tnl.nom_concepto_id')
            ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_novedades_tnl.nom_contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->where([['nom_novedades_tnl.tipo_novedad_tnl', '=', 'vacaciones']])
            ->select(
                'core_terceros.descripcion AS EMPLEADO',
                'nom_novedades_tnl.fecha_inicial_tnl AS INICIO',
                'nom_novedades_tnl.fecha_final_tnl AS FIN',
                'nom_novedades_tnl.cantidad_dias_tnl AS CANT_DÍAS',
                'nom_novedades_tnl.cantidad_dias_amortizados AS CANT_DÍAS_AMORTIZADOS',
                'nom_novedades_tnl.observaciones AS OBSERVACIONES',
                'nom_novedades_tnl.estado AS ESTADO'
            )
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PROGRAMACIÓN DE VACACIONES";
    }

    public function preparar_request_validacion($request, $id = null)
    {
        if ($request->dias_compensados === null || $request->dias_compensados === '') {
            $request->merge(['dias_compensados' => 0]);
        }

        $contratoId = $request->nom_contrato_id;
        if (empty($contratoId) && !is_null($id)) {
            $programacion = self::find($id);
            $contratoId = is_null($programacion) ? null : $programacion->nom_contrato_id;
        }

        try {
            $request->merge($this->calcularDatosProgramacion($request->all(), $contratoId));
        } catch (\InvalidArgumentException $e) {
            // La validación del formulario mostrará el mensaje específico.
        }
    }

    public function validar_datos_creacion($request, $controller)
    {
        $this->validarDatosFormulario($request, $controller);
    }

    public function validar_datos_actualizacion($request, $controller, $id)
    {
        $this->validarDatosFormulario($request, $controller, $id);
    }

    protected function validarDatosFormulario($request, $controller, $id = null)
    {
        $controller->validate($request, [
            'nom_contrato_id' => 'required|integer|exists:nom_contratos,id',
            'fecha_inicial_tnl' => 'required|date_format:Y-m-d',
            'cantidad_dias_tomados' => 'required|integer|min:1',
            'dias_compensados' => 'required|integer|min:0',
        ], [
            'cantidad_dias_tomados.integer' => 'Los días tomados deben ser un número entero.',
            'cantidad_dias_tomados.min' => 'Debe reconocer al menos un día de vacaciones.',
            'dias_compensados.integer' => 'Los días compensados deben ser un número entero.',
            'dias_compensados.min' => 'Los días compensados no pueden ser negativos.',
        ]);

        try {
            $calculo = $this->calcularDatosProgramacion($request->all(), $request->nom_contrato_id);
        } catch (\InvalidArgumentException $e) {
            $request->merge(['validacion_programacion_vacaciones' => 'invalida']);
            $controller->validate($request, [
                'validacion_programacion_vacaciones' => 'in:valida',
            ], [
                'validacion_programacion_vacaciones.in' => $e->getMessage(),
            ]);
            return;
        }

        $contrato = NomContrato::find($request->nom_contrato_id);
        if ($request->fecha_inicial_tnl < $contrato->fecha_ingreso) {
            $this->generarErrorValidacion(
                $request,
                $controller,
                'La fecha inicial de vacaciones no puede ser anterior al inicio del contrato.'
            );
        }

        if (!empty($contrato->contrato_hasta)
            && $contrato->contrato_hasta !== '0000-00-00'
            && $calculo['fecha_final_tnl'] > $contrato->contrato_hasta) {
            $this->generarErrorValidacion(
                $request,
                $controller,
                'La fecha final de vacaciones no puede superar la fecha final del contrato.'
            );
        }

        $request->merge($calculo);
    }

    protected function generarErrorValidacion($request, $controller, $mensaje)
    {
        $request->merge(['validacion_programacion_vacaciones' => 'invalida']);
        $controller->validate($request, [
            'validacion_programacion_vacaciones' => 'in:valida',
        ], [
            'validacion_programacion_vacaciones.in' => $mensaje,
        ]);
    }

    protected function calcularDatosProgramacion(array $datos, $contratoId)
    {
        $contrato = NomContrato::find($contratoId);
        if (is_null($contrato)) {
            throw new \InvalidArgumentException('El contrato seleccionado no existe.');
        }

        $calculo = (new CalculoFechaVacaciones())->calcular(
            $contrato->grupo_empleado_id,
            isset($datos['fecha_inicial_tnl']) ? $datos['fecha_inicial_tnl'] : '',
            isset($datos['cantidad_dias_tomados']) ? $datos['cantidad_dias_tomados'] : null,
            isset($datos['dias_compensados']) && $datos['dias_compensados'] !== '' ? $datos['dias_compensados'] : 0
        );

        return [
            'fecha_final_tnl' => $calculo['fecha_fin'],
            'dias_no_habiles' => $calculo['dias_no_habiles'],
            'dias_disfrutados' => $calculo['dias_disfrutados'],
            'cantidad_dias_tnl' => $calculo['cantidad_dias_tnl'],
            'cantidad_horas_tnl' => NovedadTnl::calcular_horas_tnl(
                $calculo['cantidad_dias_tnl'],
                $calculo['fecha_fin']
            ),
        ];
    }

    public static function opciones_campo_select()
    {
        $opciones = ProgramacionVacacion::where('nom_novedades_tnl.estado', 'Activo')
            ->select('nom_novedades_tnl.id', 'nom_novedades_tnl.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional($datos, $registro)
    {
        $datos = array_merge($datos, $this->calcularDatosProgramacion($datos, $registro->nom_contrato_id));
        LibroVacacion::create(
                                ['nom_contrato_id' => $registro->nom_contrato_id] +
                                    ['novedad_tnl_id' => $registro->id] +
                                    ['periodo_disfrute_vacacion_desde' => $datos['fecha_inicial_tnl']] +
                                    ['periodo_disfrute_vacacion_hasta' => $datos['fecha_final_tnl']] +
                                    ['dias_pagados' => $datos['cantidad_dias_tomados']] +
                                    ['dias_compensados' => $datos['dias_compensados']] +
                                    ['dias_no_habiles' => $datos['dias_no_habiles']] +
                                    ['dias_disfrutados' => $datos['dias_disfrutados']]
                            );

        $empleado = NomContrato::find($registro->nom_contrato_id);

        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion', 'vacaciones')
            ->where('grupo_empleado_id', $empleado->grupo_empleado_id)
            ->get()->first();
        $concepto = NomConcepto::find($parametros_prestacion->nom_concepto_id);

        $registro->tipo_novedad_tnl = 'vacaciones';
        $registro->fecha_final_tnl = $datos['fecha_final_tnl'];
        $registro->cantidad_dias_tnl = $datos['cantidad_dias_tnl'];
        $registro->cantidad_horas_tnl = $datos['cantidad_horas_tnl'];
        $registro->cantidad_dias_pendientes_amortizar = $datos['cantidad_dias_tnl'];
        $registro->nom_concepto_id = $concepto->id;
        $registro->estado = 'Activo';
        $registro->save();
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $libro_vacaciones = LibroVacacion::where('novedad_tnl_id', $registro->id)->get()->first();

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            switch ($lista_campos[$i]['name']) {
                case 'cantidad_dias_tomados':
                    $lista_campos[$i]['value'] = $libro_vacaciones->dias_pagados;
                    break;
                case 'dias_compensados':
                    $lista_campos[$i]['value'] = $libro_vacaciones->dias_compensados;
                    break;
                case 'dias_no_habiles':
                    $lista_campos[$i]['value'] = $libro_vacaciones->dias_no_habiles;
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }

    public function update_adicional($datos, $id)
    {
        $programacion = self::findOrFail($id);
        $datos = array_merge($datos, $this->calcularDatosProgramacion($datos, $programacion->nom_contrato_id));

        $programacion->fecha_final_tnl = $datos['fecha_final_tnl'];
        $programacion->cantidad_dias_tnl = $datos['cantidad_dias_tnl'];
        $programacion->cantidad_horas_tnl = $datos['cantidad_horas_tnl'];
        $programacion->cantidad_dias_pendientes_amortizar = max(
            0,
            $datos['cantidad_dias_tnl'] - $programacion->cantidad_dias_amortizados
        );
        $programacion->save();

        $libro_vacaciones = LibroVacacion::where('novedad_tnl_id', $id)->get()->first();
        $libro_vacaciones->periodo_disfrute_vacacion_desde = $datos['fecha_inicial_tnl'];
        $libro_vacaciones->periodo_disfrute_vacacion_hasta = $datos['fecha_final_tnl'];
        $libro_vacaciones->dias_pagados = $datos['cantidad_dias_tomados'];
        $libro_vacaciones->dias_compensados = $datos['dias_compensados'];
        $libro_vacaciones->dias_no_habiles = $datos['dias_no_habiles'];
        $libro_vacaciones->dias_disfrutados = $datos['dias_disfrutados'];
        $libro_vacaciones->save();
    }

    public function validar_eliminacion($id)
    {
        if (ProgramacionVacacion::find($id)->cantidad_dias_amortizados != 0) {
            return 'Ya tiene días amortizados.';
        }

        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_doc_registros",
                                    "llave_foranea":"novedad_tnl_id",
                                    "mensaje":"Ya tiene movimientos amortizados en registros de documentos de nómina."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
