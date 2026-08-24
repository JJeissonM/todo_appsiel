<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

use App\Contabilidad\ContabCuenta;
use Illuminate\Support\Facades\DB;

class ParametroLiquidacionPrestacionesSociales extends Model
{
    const BASE_SUELDO = 'sueldo';
    const BASE_SUELDO_MAS_PROMEDIO_AGRUPACION = 'sueldo_mas_promedio_agrupacion';
    const BASE_PROMEDIO_AGRUPACION = 'promedio_agrupacion';

    protected $table = 'nom_parametros_liquidacion_prestaciones_sociales';

    /*
        concepto_prestacion = { vacaciones | prima_legal | cesantias | intereses_cesantias }
        base_liquidacion= { 
                            sueldo: solo el sueldo del contrato 
                            sueldo_mas_promedio_agrupacion: sueldo del contrato + promedios de la agrupación
                            promedio_agrupacion: solo promedios de la agrupación (se debe incluir el sueldo en la agrupación para que lo tenga en cuenta)
                        }
    */
    protected $fillable = [ 'concepto_prestacion', 'grupo_empleado_id', 'nom_agrupacion_id', 'nom_concepto_id', 'nom_agrupacion2_id', 'base_liquidacion', 'cantidad_meses_a_promediar', 'dias_a_liquidar', 'sabado_es_dia_habil', 'contab_cuenta_db_id', 'contab_cuenta_cr_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Prestación', 'Grupo empleados', 'Agrupación de conceptos', 'Concepto', 'Base liquidación', 'Cantidad meses a promediar', 'Días a liquidar'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public function agrupacion_conceptos()
    {
        return $this->belongsTo(AgrupacionConcepto::class, 'nom_agrupacion_id');
    }

    public function validar_configuracion($tipo_liquidacion = null)
    {
        $bases_validas = [
            self::BASE_SUELDO,
            self::BASE_SUELDO_MAS_PROMEDIO_AGRUPACION,
            self::BASE_PROMEDIO_AGRUPACION
        ];

        if (!in_array($this->base_liquidacion, $bases_validas, true)) {
            return 'La base de liquidación configurada no es válida.';
        }

        if ((int)$this->nom_concepto_id <= 0 || is_null(NomConcepto::find($this->nom_concepto_id))) {
            return 'La prestación no tiene asociado un concepto de nómina válido.';
        }

        if (!$this->base_requiere_agrupacion()) {
            return '';
        }

        $error = $this->validar_agrupacion($this->nom_agrupacion_id, 'La agrupación de conceptos');
        if ($error !== '') {
            return $error;
        }

        if ($this->concepto_prestacion === 'vacaciones' && $tipo_liquidacion === 'terminacion_contrato') {
            return $this->validar_agrupacion(
                $this->nom_agrupacion2_id,
                'La agrupación de conceptos para terminación de contrato'
            );
        }

        return '';
    }

    public function base_requiere_agrupacion()
    {
        return in_array($this->base_liquidacion, [
            self::BASE_SUELDO_MAS_PROMEDIO_AGRUPACION,
            self::BASE_PROMEDIO_AGRUPACION
        ], true);
    }

    protected function validar_agrupacion($agrupacion_id, $nombre_campo)
    {
        if ((int)$agrupacion_id <= 0) {
            return $nombre_campo . ' es obligatoria para la base de liquidación seleccionada.';
        }

        $agrupacion = AgrupacionConcepto::find($agrupacion_id);
        if (is_null($agrupacion)) {
            return $nombre_campo . ' no existe.';
        }

        if (!$agrupacion->conceptos()->exists()) {
            return $nombre_campo . ' no tiene conceptos asociados.';
        }

        return '';
    }

    public function validar_datos_creacion($request, $controller)
    {
        $this->validar_datos_formulario($request, $controller);
    }

    public function validar_datos_actualizacion($request, $controller, $id)
    {
        $this->validar_datos_formulario($request, $controller);
    }

    protected function validar_datos_formulario($request, $controller)
    {
        $controller->validate($request, [
            'base_liquidacion' => 'required|in:' . implode(',', [
                self::BASE_SUELDO,
                self::BASE_SUELDO_MAS_PROMEDIO_AGRUPACION,
                self::BASE_PROMEDIO_AGRUPACION
            ]),
            'nom_concepto_id' => 'required|not_in:0|exists:nom_conceptos,id'
        ], [
            'base_liquidacion.in' => 'La base de liquidación seleccionada no es válida.',
            'nom_concepto_id.not_in' => 'Debe seleccionar el concepto de nómina de la prestación.',
            'nom_concepto_id.exists' => 'El concepto de nómina seleccionado no existe.'
        ]);

        if (!in_array($request->base_liquidacion, [
            self::BASE_SUELDO_MAS_PROMEDIO_AGRUPACION,
            self::BASE_PROMEDIO_AGRUPACION
        ], true)) {
            return;
        }

        $controller->validate($request, [
            'nom_agrupacion_id' => 'required|not_in:0|exists:nom_agrupaciones_conceptos,id'
        ], [
            'nom_agrupacion_id.required' => 'Debe seleccionar una agrupación de conceptos.',
            'nom_agrupacion_id.not_in' => 'Debe seleccionar una agrupación de conceptos.',
            'nom_agrupacion_id.exists' => 'La agrupación de conceptos seleccionada no existe.'
        ]);

        $agrupacion = AgrupacionConcepto::find($request->nom_agrupacion_id);
        if (!is_null($agrupacion) && !$agrupacion->conceptos()->exists()) {
            $controller->validate($request, [
                'nom_agrupacion_id' => 'in:__agrupacion_con_conceptos__'
            ], [
                'nom_agrupacion_id.in' => 'La agrupación seleccionada no tiene conceptos asociados.'
            ]);
        }

        if ($request->concepto_prestacion !== 'vacaciones') {
            return;
        }

        $controller->validate($request, [
            'nom_agrupacion2_id' => 'required|not_in:0|exists:nom_agrupaciones_conceptos,id'
        ], [
            'nom_agrupacion2_id.required' => 'Debe seleccionar la agrupación para vacaciones por terminación de contrato.',
            'nom_agrupacion2_id.not_in' => 'Debe seleccionar la agrupación para vacaciones por terminación de contrato.',
            'nom_agrupacion2_id.exists' => 'La agrupación para vacaciones por terminación de contrato no existe.'
        ]);

        $agrupacion_terminacion = AgrupacionConcepto::find($request->nom_agrupacion2_id);
        if (!is_null($agrupacion_terminacion) && !$agrupacion_terminacion->conceptos()->exists()) {
            $controller->validate($request, [
                'nom_agrupacion2_id' => 'in:__agrupacion_con_conceptos__'
            ], [
                'nom_agrupacion2_id.in' => 'La agrupación para terminación de contrato no tiene conceptos asociados.'
            ]);
        }
    }

    public function cuenta_debito()
    {
        return $this->belongsTo( ContabCuenta::class, 'contab_cuenta_db_id');
    }

    public function cuenta_credito()
    {
        return $this->belongsTo( ContabCuenta::class, 'contab_cuenta_cr_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return ParametroLiquidacionPrestacionesSociales::leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.grupo_empleado_id')
            ->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.nom_agrupacion_id')
            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.nom_concepto_id')
            ->select(
                'nom_parametros_liquidacion_prestaciones_sociales.concepto_prestacion AS campo1',
                'nom_grupos_empleados.descripcion AS campo2',
                'nom_agrupaciones_conceptos.descripcion AS campo3',
                DB::raw('CONCAT(nom_conceptos.id," - ",nom_conceptos.descripcion) AS campo4'),
                'nom_parametros_liquidacion_prestaciones_sociales.base_liquidacion AS campo5',
                'nom_parametros_liquidacion_prestaciones_sociales.cantidad_meses_a_promediar AS campo6',
                'nom_parametros_liquidacion_prestaciones_sociales.dias_a_liquidar AS campo7',
                'nom_parametros_liquidacion_prestaciones_sociales.id AS campo8'
            )
            ->where("nom_parametros_liquidacion_prestaciones_sociales.concepto_prestacion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_agrupaciones_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(nom_conceptos.id," - ",nom_conceptos.descripcion)'), "LIKE", "%$search%")
            ->orWhere("nom_parametros_liquidacion_prestaciones_sociales.base_liquidacion", "LIKE", "%$search%")
            ->orWhere("nom_parametros_liquidacion_prestaciones_sociales.cantidad_meses_a_promediar", "LIKE", "%$search%")
            ->orWhere("nom_parametros_liquidacion_prestaciones_sociales.dias_a_liquidar", "LIKE", "%$search%")
            ->orderBy('nom_parametros_liquidacion_prestaciones_sociales.created_at', 'DESC')
            ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $string = ParametroLiquidacionPrestacionesSociales::leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.grupo_empleado_id')
            ->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.nom_agrupacion_id')
            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.nom_concepto_id')
            ->select(
                'nom_parametros_liquidacion_prestaciones_sociales.concepto_prestacion AS PRESTACIÓN',
                'nom_grupos_empleados.descripcion AS GRUPO_EMPLEADOS',
                'nom_agrupaciones_conceptos.descripcion AS AGRUPACIÓN_DE_CONCEPTOS',
                DB::raw('CONCAT(nom_conceptos.id," - ",nom_conceptos.descripcion) AS CONCEPTO'),
                'nom_parametros_liquidacion_prestaciones_sociales.base_liquidacion AS BASE_LIQUIDACIÓN',
                'nom_parametros_liquidacion_prestaciones_sociales.cantidad_meses_a_promediar AS CANTIDAD_MESES_A_PROMEDIAR',
                'nom_parametros_liquidacion_prestaciones_sociales.dias_a_liquidar AS DÍAS_A_LIQUIDAR'
            )
            ->where("nom_parametros_liquidacion_prestaciones_sociales.concepto_prestacion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_agrupaciones_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(nom_conceptos.id," - ",nom_conceptos.descripcion)'), "LIKE", "%$search%")
            ->orWhere("nom_parametros_liquidacion_prestaciones_sociales.base_liquidacion", "LIKE", "%$search%")
            ->orWhere("nom_parametros_liquidacion_prestaciones_sociales.cantidad_meses_a_promediar", "LIKE", "%$search%")
            ->orWhere("nom_parametros_liquidacion_prestaciones_sociales.dias_a_liquidar", "LIKE", "%$search%")
            ->orderBy('nom_parametros_liquidacion_prestaciones_sociales.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE LIQUIDACION PRESTACIONES SOCIALES";
    }

    public static function opciones_campo_select()
    {
        $opciones = ParametroLiquidacionPrestacionesSociales::where('nom_parametros_liquidacion_prestaciones_sociales.estado', 'Activo')
            ->select('nom_parametros_liquidacion_prestaciones_sociales.id', 'nom_parametros_liquidacion_prestaciones_sociales.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function get_fecha_inicial_promedios( $fecha_final, $empleado )
    {
        if ( $empleado->estado == 'Retirado' )
        {
            $fecha_ultima_liquidacion = $this->get_fecha_ultima_liquidacion( $empleado, $this->concepto_prestacion );

            if ( $fecha_ultima_liquidacion != null )
            {
                return $this->sumar_dias_calendario_30_dias_a_fecha( $fecha_ultima_liquidacion, 1 );
            }
        }            

        $vec_fecha_documento = explode("-", $fecha_final);
        
        $anio_final = (int)$vec_fecha_documento[0];
        $mes_final = (int)$vec_fecha_documento[1];
        $dia_final = (int)$vec_fecha_documento[2];

        $anio_inicial = $anio_final;
        $mes_inicial = $mes_final - 1; // Un mes atrás

        /*
            El día inicial es un día después del dia final, si se pasa de treinta, 31 o 28 para febrero, día incial es 01
        */
        $dia_inicial = $this->formatear_numero_a_texto_dos_digitos( $dia_final + 1 ); // Un días después del día final
        if ( $dia_final >= 30 )
        {
            $dia_inicial = '01';
            $mes_inicial++;
        }
        if ( ($mes_final == 2) && ($dia_final >= 28) ) // Febrero
        {
            $dia_inicial = '01';
            $mes_inicial++;
        }

        for ( $i = $this->cantidad_meses_a_promediar; $i > 1; $i--)
        {
            if ( $mes_inicial == 0 )
            {
                $mes_inicial = 11;
                $anio_inicial = $anio_final - 1;
            }else{
                $mes_inicial--;
            }
        }

        if ( $mes_inicial == 0 )
        {
            $mes_inicial = 1;
        }

        $mes_inicial = $this->formatear_numero_a_texto_dos_digitos( $mes_inicial );

        $fecha_inicial = $anio_inicial . '-' . $mes_inicial . '-' . $dia_inicial;

        $diferencia = $this->diferencia_en_dias_entre_fechas( $fecha_inicial, $empleado->fecha_ingreso );
        
        // Si la fecha_inicial es menor que la fecha_ingreso del empleado, la fecha inicial debe ser la del contrato
        if ( $diferencia > 0 )
        {
            return $empleado->fecha_ingreso;
        }

        // Si la diferencia es negativa, quiere decir que la fecha_final es superior a la fecha_ingreso
        return $fecha_inicial;
    }

    
    public function get_fecha_ultima_liquidacion( $empleado, $prestacion )
    {
        $prestaciones_liquidadas_empleado = PrestacionesLiquidadas::where( 'nom_contrato_id', $empleado->id )
                                                                    ->orderBy('fecha_final_promedios')
                                                                    ->orderBy('id')
                                                                    ->get();

        $fecha_ultima_liquidacion = null;
        foreach ( $prestaciones_liquidadas_empleado as $registro )
        {
            $prestaciones_liquidadas = PrestacionesLiquidadas::get_prestaciones_desde_json( $registro->prestaciones_liquidadas );

            foreach ( $prestaciones_liquidadas as $prestacion_liquidada )
            {
                if( !isset($prestacion_liquidada->prestacion) || $prestacion_liquidada->prestacion != $prestacion  )
                {
                    continue;
                }

                $tabla_resumen = (array)$prestacion_liquidada->tabla_resumen;
                if ( $prestacion == 'vacaciones' && isset($tabla_resumen['periodo_pagado_hasta']) )
                {
                    $fecha_ultima_liquidacion = $tabla_resumen['periodo_pagado_hasta'];
                }

                if ( $prestacion != 'vacaciones' && isset($tabla_resumen['fecha_liquidacion']) )
                {
                    $fecha_ultima_liquidacion = $tabla_resumen['fecha_liquidacion'];
                }
            }
        }
        
        return $fecha_ultima_liquidacion;
    }
    /**/

    public function formatear_numero_a_texto_dos_digitos( $numero )
    {
        if ( strlen($numero) == 1 )
        {
            return "0" . $numero;
        }

        return $numero;
    }

    public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return $fecha_ini->diffInDays( $fecha_fin, false); // false activa el calculo de diferencias negativas
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{}';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }

    public function sumar_dias_calendario_a_fecha( string $fecha, int $cantidad_dias )
    {
        $fecha_aux = Carbon::createFromFormat('Y-m-d', $fecha );

        return $fecha_aux->addDays( $cantidad_dias )->format('Y-m-d');
    }

    public function sumar_dias_calendario_30_dias_a_fecha( string $fecha, int $cantidad_dias )
    {
        $fecha_aux = $this->sumar_dias_calendario_a_fecha( $fecha, $cantidad_dias );

        $vec_fecha = explode('-', $fecha_aux);
        $anio = (int)$vec_fecha[0];
        $mes = (int)$vec_fecha[1];
        $dia = (int)$vec_fecha[2];

        if ( $dia == 31 )
        {
            $dia = 1;

            if ( $mes == 12 ) // Si es Diciembre
            {
                $anio++;
                $mes = 1;
            }else{
                $mes++;
            }
        }
        
        return $anio . '-' . $this->formatear_numero_a_texto_dos_digitos( $mes ) . '-' . $this->formatear_numero_a_texto_dos_digitos( $dia );
    }
}
