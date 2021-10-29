<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use App\Core\Colegio;
use App\Core\ModeloEavValor;
use App\Sistema\Modelo;

use Auth;
use DB;

use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Calificacion;

class Periodo extends Model
{
    protected $table='sga_periodos';

    protected $fillable = ['periodo_lectivo_id','id_colegio','numero', 'descripcion','fecha_desde','fecha_hasta', 'periodo_de_promedios', 'estado', 'cerrado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año lectivo', 'Número', 'Descripcion', 'Fecha desde', 'Fecha hasta', 'Cerrado', 'Periodo de promedios', 'Estado'];

    public function observaciones_boletin( $estudiante_id )
    {
        return ObservacionesBoletin::where([
                                    ['id_periodo','=', $this->id ],
                                    ['id_estudiante','=', $estudiante_id ]
                                ])->get();
    }

    public function calificaciones_asignaturas_perdidas( $estudiante_id )
    {
        $escala_valoracion = EscalaValoracion::get_rango_minimo();
        
        return Calificacion::where([
                                    ['calificacion','<=',$escala_valoracion[1] ],
                                    ['id_periodo','=', $this->id ],
                                    ['id_estudiante','=', $estudiante_id ]
                                ])->get();
    }

    public function get_calificacion( $curso_id, $estudiante_id, $asignatura_id )
    {
        return Calificacion::get_para_boletin( $this->id, $curso_id, $estudiante_id, $asignatura_id );
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'IF(sga_periodos.cerrado=0,REPLACE(sga_periodos.cerrado,0,"No"),REPLACE(sga_periodos.cerrado,1,"Si")) AS campo6';

        $registros = Periodo::leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->select(
                'sga_periodos_lectivos.descripcion AS campo1',
                'sga_periodos.numero AS campo2',
                'sga_periodos.descripcion AS campo3',
                'sga_periodos.fecha_desde AS campo4',
                'sga_periodos.fecha_hasta AS campo5',
                DB::raw($select_raw),
                DB::raw('IF(sga_periodos.periodo_de_promedios=0,REPLACE(sga_periodos.periodo_de_promedios,0,"No"),REPLACE(sga_periodos.periodo_de_promedios,1,"Si")) AS campo7'),
                'sga_periodos.estado AS campo8',
                'sga_periodos.id AS campo9'
            )->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.numero", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.fecha_desde", "LIKE", "%$search%")
            ->orWhere("sga_periodos.fecha_hasta", "LIKE", "%$search%")
            ->orWhere(DB::raw('IF(sga_periodos.cerrado=0,REPLACE(sga_periodos.cerrado,0,"No"),REPLACE(sga_periodos.cerrado,1,"Si"))'), "LIKE", "%$search%")
            ->orWhere(DB::raw('IF(sga_periodos.periodo_de_promedios=0,REPLACE(sga_periodos.periodo_de_promedios,0,"No"),REPLACE(sga_periodos.periodo_de_promedios,1,"Si"))'), "LIKE", "%$search%")
            ->orWhere("sga_periodos.estado", "LIKE", "%$search%")
            ->orderBy('sga_periodos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'IF(sga_periodos.cerrado=0,REPLACE(sga_periodos.cerrado,0,"No"),REPLACE(sga_periodos.cerrado,1,"Si")) AS CERRADO';

        $string = Periodo::leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
            ->select(
                'sga_periodos_lectivos.descripcion AS AÑO_LECTIVO',
                'sga_periodos.numero AS NÚMERO',
                'sga_periodos.descripcion AS DESCRIPCION',
                'sga_periodos.fecha_desde AS FECHA_DESDE',
                'sga_periodos.fecha_hasta AS FECHA_HASTA',
                DB::raw($select_raw),
                DB::raw('IF(sga_periodos.periodo_de_promedios=0,REPLACE(sga_periodos.periodo_de_promedios,0,"No"),REPLACE(sga_periodos.periodo_de_promedios,1,"Si")) AS PERIODO_DE_PROMEDIOS'),
                'sga_periodos.estado AS ESTADO'
            )->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.numero", "LIKE", "%$search%")
            ->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos.fecha_desde", "LIKE", "%$search%")
            ->orWhere("sga_periodos.fecha_hasta", "LIKE", "%$search%")
            ->orWhere(DB::raw('IF(sga_periodos.cerrado=0,REPLACE(sga_periodos.cerrado,0,"No"),REPLACE(sga_periodos.cerrado,1,"Si"))'), "LIKE", "%$search%")
            ->orWhere(DB::raw('IF(sga_periodos.periodo_de_promedios=0,REPLACE(sga_periodos.periodo_de_promedios,0,"No"),REPLACE(sga_periodos.periodo_de_promedios,1,"Si"))'), "LIKE", "%$search%")
            ->orWhere("sga_periodos.estado", "LIKE", "%$search%")
            ->orderBy('sga_periodos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PERIODO";
    }

    // El archivo js debe estar en la carpeta public
    //public $archivo_js = 'assets/js/calificaciones_periodos.js';

    public static function opciones_campo_select()
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $opciones = Periodo::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_periodos.periodo_lectivo_id')
                            ->where('sga_periodos_lectivos.cerrado',0)
                            ->where('sga_periodos.id_colegio',$colegio->id)
                            ->where('sga_periodos.estado','Activo')
                            ->where('sga_periodos.cerrado',0)
                            ->select(
                                        'sga_periodos.id',
                                        'sga_periodos.descripcion',
                                        'sga_periodos.fecha_desde',
                                        'sga_periodos_lectivos.descripcion AS periodo_lectivo_descripcion',
                                        'sga_periodos.periodo_de_promedios')
                            ->orderBy('sga_periodos_lectivos.id')
                            ->orderBy('sga_periodos.numero')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->periodo_lectivo_descripcion . ' > ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_activos_periodo_lectivo( $periodo_lectivo_id = null )
    {
        $array_wheres = [ [ 'sga_periodos.id_colegio', '>', 0] ];
        
        if ( !is_null( $periodo_lectivo_id ) ) 
        {
            $array_wheres = array_merge($array_wheres, [ [ 'sga_periodos.periodo_lectivo_id', $periodo_lectivo_id ] ]);          
        }

        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        return Periodo::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_periodos.periodo_lectivo_id')
                            ->where('sga_periodos.estado','Activo')
                            ->where($array_wheres)
                            ->select(
                                        'sga_periodos.id',
                                        'sga_periodos.periodo_lectivo_id',
                                        'sga_periodos.descripcion',
                                        'sga_periodos.numero',
                                        'sga_periodos.periodo_de_promedios',
                                        'sga_periodos.fecha_desde',
                                        'sga_periodos_lectivos.descripcion AS periodo_lectivo_descripcion',
                                        'sga_periodos.fecha_hasta')
                            ->orderBy('sga_periodos_lectivos.id')
                            ->orderBy('sga_periodos.numero')
                            ->get();
    }

    public static function get_activos_periodo_lectivo_actual()
    {
        return Periodo::get_activos_periodo_lectivo( PeriodoLectivo::get_actual()->id );
    }

    public static function get_array_to_select($colegio_id,$cerrado)
    {
        // Para el campo cerrado: 0 = cerrado, 1 = abierto, '' = cualquiera
        $opciones = Periodo::where('id_colegio','=',$colegio_id)
                            ->where('estado','=','Activo')
                            ->where('cerrado','LIKE','%'.$cerrado.'%')
                            ->orderBy('numero')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->descripcion;
        }
        
        return $vec;
    }

    public function store_adicional( $datos, $registro )
    {
        $modelo_padre_id = Modelo::where('modelo', 'periodos')->value('id');

        $this->almacenar_registros_eav( $datos, $modelo_padre_id, $registro->id );
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $modelo_padre_id = Modelo::where('modelo', 'periodos')->value('id');

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $lista_campos[$i]['name'], "core_campo_id") !== false ) 
            {
                $core_campo_id = $lista_campos[$i]['id']; // Atributo_ID

                $registro_eav = ModeloEavValor::where(
                                                    [ 
                                                        "modelo_padre_id" => $modelo_padre_id,
                                                        "registro_modelo_padre_id" => $registro->id,
                                                        "core_campo_id" => $core_campo_id
                                                    ]
                                                )
                                            ->get()
                                            ->first();
                if( !is_null( $registro_eav ) )
                {
                    $lista_campos[$i]['value'] = $registro_eav->valor;
                }
            }

        }

        return $lista_campos;
    }

    public function update_adicional( $datos, $id )
    {
        $modelo_padre_id = Modelo::where('modelo', 'periodos')->value('id');

        $this->almacenar_registros_eav( $datos, $modelo_padre_id, $id );        
    }

    // $datos = $request->all()
    public function almacenar_registros_eav( $datos, $modelo_padre_id, $registro_modelo_padre_id )
    {
        // Se va a crear un registro por cada Atributo (campo) que tenga un Valor distinto a vacío 
        foreach ( $datos as $key => $value) 
        {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if ( strpos( $key, "core_campo_id") !== false ) 
            {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor

                $registro_eav = ModeloEavValor::where(
                                                        [ 
                                                            "modelo_padre_id" => $modelo_padre_id,
                                                            "registro_modelo_padre_id" => $registro_modelo_padre_id,
                                                            "core_campo_id" => $core_campo_id
                                                        ]
                                                    )
                                                ->get()
                                                ->first();

                if ( is_null( $registro_eav ) )
                {
                    ModeloEavValor::create( [ "modelo_padre_id" => $modelo_padre_id, "registro_modelo_padre_id" => $registro_modelo_padre_id, "modelo_entidad_id" => 0, "core_campo_id" => $core_campo_id, "valor" => $valor ] );
                }else{
                    $registro_eav->valor = $valor;
                    $registro_eav->save();
                }
            }
        }
    } 
}
