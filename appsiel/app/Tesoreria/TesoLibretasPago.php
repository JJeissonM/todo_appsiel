<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TesoLibretasPago extends Model
{
    protected $table = 'teso_libretas_pagos';

    protected $fillable = ['id_estudiante','matricula_id','fecha_inicio','valor_matricula','valor_pension_anual','numero_periodos','valor_pension_mensual','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Curso', 'Cód. Matricula', 'Fecha inicio', 'Vlr. Matrícula', 'Vlr. Pensión anual', 'No. periodos', 'Vlr. Pensión mes', 'Estado'];

    // El archivo js debe estar en la carpeta public
    //public $archivo_js = 'assets/js/tesoreria/libreta_pagos_estudiantes.js';

    public function estudiante()
    {
        return $this->belongsTo( 'App\Matriculas\Estudiante', 'id_estudiante' );
    }

    public function lineas_registros_plan_pagos()
    {
        return $this->hasMany( TesoPlanPagosEstudiante::class, 'id_libreta' );
    }

    public function actualizar_estado()
    {
        $suma_matriculas = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto( $this->id, config('matriculas.inv_producto_id_default_matricula') );
        $suma_pensiones = TesoPlanPagosEstudiante::get_total_valor_pagado_concepto( $this->id, config('matriculas.inv_producto_id_default_pension') );
        $total_pagado = $suma_matriculas + $suma_pensiones ;

        $total_libreta = $this->valor_matricula + ($this->valor_pension_mensual * $this->numero_periodos);

        $this->estado = "Activo";
        if ( $total_pagado == $total_libreta )
        {
            $this->estado = "Inactivo";
        }
        
        $this->save();
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        }

        return TesoLibretasPago::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'teso_libretas_pagos.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_matriculas', 'sga_matriculas.id', '=', 'teso_libretas_pagos.matricula_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
            ->select(
                DB::raw($raw_nombre_completo),
                'sga_cursos.descripcion AS campo2',
                'sga_matriculas.codigo AS campo3',
                'teso_libretas_pagos.fecha_inicio AS campo4',
                'teso_libretas_pagos.valor_matricula AS campo5',
                'teso_libretas_pagos.valor_pension_anual AS campo6',
                'teso_libretas_pagos.numero_periodos AS campo7',
                'teso_libretas_pagos.valor_pension_mensual AS campo8',
                'teso_libretas_pagos.estado AS campo9',
                'teso_libretas_pagos.id AS campo10'
            )
            ->where(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.codigo", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.fecha_inicio", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_matricula", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_anual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.numero_periodos", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_mensual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.estado", "LIKE", "%$search%")

            ->orderBy('teso_libretas_pagos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS ESTUDIANTE';
        }

        $string = TesoLibretasPago::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'teso_libretas_pagos.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('sga_matriculas', 'sga_matriculas.id', '=', 'teso_libretas_pagos.matricula_id')
            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
            ->select(
                DB::raw($raw_nombre_completo),
                'sga_cursos.descripcion AS CURSO',
                'sga_matriculas.codigo AS CÓD._MATRICULA',
                'teso_libretas_pagos.fecha_inicio AS FECHA_INICIO',
                'teso_libretas_pagos.valor_matricula AS VLR._MATRÍCULA',
                'teso_libretas_pagos.valor_pension_anual AS VLR._PENSIÓN_ANUAL',
                'teso_libretas_pagos.numero_periodos AS NO._PERIODOS',
                'teso_libretas_pagos.valor_pension_mensual AS VLR._PENSIÓN_MES',
                'teso_libretas_pagos.estado AS ESTADO'
            )
            ->where(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_matriculas.codigo", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.fecha_inicio", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_matricula", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_anual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.numero_periodos", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.valor_pension_mensual", "LIKE", "%$search%")
            ->orWhere("teso_libretas_pagos.estado", "LIKE", "%$search%")

            ->orderBy('teso_libretas_pagos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE LIBRETAS DE PAGO";
    }

    public static function consultar_un_registro($id)
    {
        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';
        }

        $registros = TesoLibretasPago::leftJoin('sga_estudiantes','sga_estudiantes.id','=','teso_libretas_pagos.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->leftJoin('sga_matriculas','sga_matriculas.id','=','teso_libretas_pagos.matricula_id')
                    ->leftJoin('sga_cursos','sga_cursos.id','=','sga_matriculas.curso_id')
                    ->where('teso_libretas_pagos.id', $id)
                    ->select(
                                DB::raw( $raw_nombre_completo ),
                                'sga_cursos.descripcion AS campo2',
                                'sga_matriculas.codigo AS campo3',
                                'teso_libretas_pagos.fecha_inicio AS campo4',
                                'teso_libretas_pagos.valor_matricula AS campo5',
                                'teso_libretas_pagos.valor_pension_anual AS campo6',
                                'teso_libretas_pagos.numero_periodos AS campo7',
                                'teso_libretas_pagos.valor_pension_mensual AS campo8',
                                'teso_libretas_pagos.estado AS campo9',
                                'teso_libretas_pagos.id AS campo10')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function get_campos_adicionales_create( $lista_campos )
    {
        $inv_producto_id_default_matricula = (int)config('matriculas.inv_producto_id_default_matricula');
        $inv_producto_id_default_pension = config('matriculas.inv_producto_id_default_pension');

        if( $inv_producto_id_default_matricula == 0 ||   $inv_producto_id_default_pension == 0 )
        {
            return [
                [
                    "id" => 999,
                    "descripcion" => "Label.",
                    "tipo" => "personalizado",
                    "name" => "lbl",
                    "opciones" => "",
                    "value" => '<div class="form-group">                    
                                                    <label class="control-label col-sm-3" style="color: red;" > <b> No se han configurado  los servicios para facturar Matrícula y Pensión. </b> </label>
                                                    <br>
                                                    <a href="' . url('config?id=1&id_modelo=0') . '" class="btn btn-sm btn-info"> <i class="fa fa-th-large"></i> Ir a la configuración. </a>      
                                                </div>',
                    "atributos" => [],
                    "definicion" => "",
                    "requerido" => 0,
                    "editable" => 1,
                    "unico" => 0
                ]
            ];
        }

        return $lista_campos;
    }
}
