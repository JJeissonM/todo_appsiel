<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

// PLAN DE PAGOS del estudiante
class TesoPlanPagosEstudiante extends Model
{
    protected $table = 'teso_cartera_estudiantes';

    // NOTA: el campo inv_producto_id corresponde al "concepto" que se usa en la facturas.
    protected $fillable = [ 'id_libreta', 'id_estudiante', 'inv_producto_id', 'valor_cartera', 'valor_pagado', 'saldo_pendiente', 'fecha_vencimiento', 'estado' ];

    public $urls_acciones = '{"edit":"web/id_fila/edit"}';

    public function libreta()
    {
        return $this->belongsTo( TesoLibretasPago::class, 'id_libreta');
    }

    public function estudiante()
    {
        return $this->belongsTo( 'App\Matriculas\Estudiante', 'id_estudiante');
    }

    public function concepto()
    {
        return $this->belongsTo( 'App\Inventarios\InvProducto', 'inv_producto_id');
    }

    public function facturas_estudiantes()
    {
        return $this->hasMany( 'App\Matriculas\FacturaAuxEstudiante', 'cartera_estudiante_id');
    }

    public function teso_doc_encabezado()
    {
        $recaudo_libreta = TesoRecaudosLibreta::where( 'id_cartera', $this->id )->get()->first();
        
        return $recaudo_libreta->recaudo_tesoreria();
    }

    public function sumar_abono_registro_cartera_estudiante( $valor_recaudo )
    {
        $valor_pagado = $this->valor_pagado + $valor_recaudo;
        $saldo_pendiente = $this->saldo_pendiente - $valor_recaudo;
        $estado = $this->estado;
        if( $valor_pagado == $this->valor_cartera )
        {
            $estado = "Pagada";
        }
        $this->valor_pagado = $valor_pagado;
        $this->saldo_pendiente = $saldo_pendiente;
        $this->estado = $estado;
        $this->save();
    }

    public function restar_abono_registro_cartera_estudiante( $valor_recaudo )
    {
        $nuevo_valor_pagado = $this->valor_pagado - $valor_recaudo;
        $saldo_pendiente = $this->saldo_pendiente + $valor_recaudo;
        
        $estado = "Pendiente";
        if($nuevo_valor_pagado == $this->valor_cartera)
        {
            $estado = "Pagada";
        }
        
        $this->valor_pagado = $nuevo_valor_pagado;
        $this->saldo_pendiente = $saldo_pendiente;
        $this->estado = $estado;
        $this->save();
    }

    public function get_registros_pendientes_o_vencidos_a_la_fecha( $fecha, $inv_producto_id = null )
    {
        if ( is_null( $inv_producto_id ) )
        {
            return TesoPlanPagosEstudiante::where( 'fecha_vencimiento', '<=', $fecha )
                                        ->orWhere(function ($query) {
                                                $query->where('estado', '=', 'Pendiente')
                                                      ->where('estado', '=', 'Vencida');
                                            })
                                        ->get();
        }else{
            return TesoPlanPagosEstudiante::where( 'fecha_vencimiento', '<=', $fecha )
                                        ->where( 'inv_producto_id', '=', $inv_producto_id )
                                        ->orWhere(function ($query) {
                                                $query->where('estado', '=', 'Pendiente')
                                                      ->where('estado', '=', 'Vencida');
                                            })
                                        ->get();
        }
    }

    public static function get_total_valor_pagado_concepto( $libreta_id, $inv_producto_id )
    {
        return TesoPlanPagosEstudiante::where('id_libreta',$libreta_id)
                                        ->where('inv_producto_id', $inv_producto_id)
                                        ->sum('valor_pagado');
    }


    public static function get_cartera_estudiantes_curso( $curso_id, $fecha_vencimiento, $concepto)
    {
        $cadena="%-".$fecha_vencimiento."-%";

        if ( $curso_id == '') 
        {
            $curso_id = '%%';
        }

        $raw_nombre_completo = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo';
        if (config('matriculas.modo_visualizacion_nombre_completo_estudiante') == 'nombres_apellidos') {
            $raw_nombre_completo = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS nombre_completo';
        }

        return TesoPlanPagosEstudiante::leftJoin('sga_estudiantes','sga_estudiantes.id','=','teso_cartera_estudiantes.id_estudiante')
                        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                        ->leftJoin('teso_libretas_pagos','teso_libretas_pagos.id_estudiante','=','sga_estudiantes.id')
                        ->leftJoin('sga_matriculas','sga_matriculas.id','=','teso_libretas_pagos.matricula_id')
                        ->leftJoin('sga_cursos','sga_cursos.id','=','sga_matriculas.curso_id')
                        ->where('sga_matriculas.curso_id', 'LIKE', $curso_id)
                        ->where('teso_cartera_estudiantes.fecha_vencimiento','LIKE', $cadena)
                        ->where('teso_cartera_estudiantes.inv_producto_id', '=', $concepto)
                        ->where('teso_cartera_estudiantes.estado','=','Vencida')
                        ->where('teso_cartera_estudiantes.saldo_pendiente','<>',0)
                        ->select(
                                    DB::raw( $raw_nombre_completo ),
                                    'core_terceros.numero_identificacion AS doc_identidad',
                                    'core_terceros.apellido1',
                                    'teso_cartera_estudiantes.valor_cartera',
                                    'teso_cartera_estudiantes.valor_pagado',
                                    'sga_matriculas.codigo AS codigo_matricula',
                                    'sga_cursos.descripcion AS nom_curso',
                                    'sga_cursos.codigo AS codigo_curso')
                        ->orderBy('sga_cursos.codigo','ASC')
                        ->orderBy('core_terceros.apellido1','ASC')
                        ->get();
    }

    public function get_campos_adicionales_edit( $lista_campos, $registro )
    {
        


        array_unshift($lista_campos, [
                                        "id" => 999,
                                        "descripcion" => "separador",
                                        "tipo" => "personalizado",
                                        "name" => "name_4",
                                        "opciones" => "",
                                        "value" => '&nbsp;',
                                        "atributos" => [],
                                        "definicion" => "",
                                        "requerido" => 0,
                                        "editable" => 1,
                                        "unico" => 0
                                    ] );

        if ( !is_null($registro->concepto) ) 
        {
            array_unshift($lista_campos, [
                                            "id" => 999,
                                            "descripcion" => "nombre_concepto",
                                            "tipo" => "personalizado",
                                            "name" => "name_3",
                                            "opciones" => "",
                                            "value" => '<div class="container-fluid"><h4> Concepto: <small>' . $registro->concepto->descripcion . '</small></h4><hr></div> <input type="hidden" value="' . $registro->id_libreta . '" name="id_libreta">',
                                            "atributos" => [],
                                            "definicion" => "",
                                            "requerido" => 0,
                                            "editable" => 1,
                                            "unico" => 0
                                        ] );
        }
        
        array_unshift($lista_campos, [
                                        "id" => 999,
                                        "descripcion" => "separador",
                                        "tipo" => "personalizado",
                                        "name" => "name_4",
                                        "opciones" => "",
                                        "value" => '&nbsp;',
                                        "atributos" => [],
                                        "definicion" => "",
                                        "requerido" => 0,
                                        "editable" => 1,
                                        "unico" => 0
                                    ] );    

        $estudiante = $registro->estudiante;
        if ( !is_null($estudiante) ) 
        {
            array_unshift($lista_campos, [
                                            "id" => 999,
                                            "descripcion" => "nombre_estudiante",
                                            "tipo" => "personalizado",
                                            "name" => "name_2",
                                            "opciones" => "",
                                            "value" => '<div class="container-fluid"><h4> Estudiante: <small>' . $estudiante->tercero->descripcion . '</small></h4><hr></div>',
                                            "atributos" => [],
                                            "definicion" => "",
                                            "requerido" => 0,
                                            "editable" => 1,
                                            "unico" => 0
                                        ] );
        }
        
        return $lista_campos;
    }

    public function update_adicional( $datos, $id )
    {
        TesoPlanPagosEstudiante::find( $id )->update( [ 'saldo_pendiente' => $datos['valor_cartera'] ] );

        return 'tesoreria/ver_plan_pagos/' . $datos['id_libreta'] . '?id=3&id_modelo=31&id_transaccion=';
    }
}
