<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use App\Core\Colegio;
use Auth;
use DB;

class Periodo extends Model
{
    protected $table='sga_periodos';

    protected $fillable = ['periodo_lectivo_id','id_colegio','numero', 'descripcion','fecha_desde','fecha_hasta', 'periodo_de_promedios', 'estado', 'cerrado'];

    public $encabezado_tabla = ['Año lectivo','Número','Descripcion','Fecha desde','Fecha hasta','Cerrado','Periodo de promedios','Estado','Acción'];

    public static function consultar_registros()
    {
    	$select_raw = 'IF(sga_periodos.cerrado=0,REPLACE(sga_periodos.cerrado,0,"No"),REPLACE(sga_periodos.cerrado,1,"Si")) AS campo6';

        $registros = Periodo::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_periodos.periodo_lectivo_id')
                            ->select('sga_periodos_lectivos.descripcion AS campo1',
                            'sga_periodos.numero AS campo2',
                            'sga_periodos.descripcion AS campo3',
                            'sga_periodos.fecha_desde AS campo4',
                            'sga_periodos.fecha_hasta AS campo5',
                            DB::raw($select_raw),
                            DB::raw('IF(sga_periodos.periodo_de_promedios=0,REPLACE(sga_periodos.periodo_de_promedios,0,"No"),REPLACE(sga_periodos.periodo_de_promedios,1,"Si")) AS campo7'),
                            'sga_periodos.estado AS campo8',
                            'sga_periodos.id AS campo9')
                    ->get()
                    ->toArray();

        return $registros;
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
                            ->orderBy('sga_periodos.numero')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion.' ('.$opcion->periodo_lectivo_descripcion.')';
        }

        return $vec;
    }

    public static function get_activos_periodo_lectivo( $periodo_lectivo_id )
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        return Periodo::where('sga_periodos.estado','Activo')
                            ->where('sga_periodos.id_colegio',$colegio->id)
                            ->where('sga_periodos.periodo_lectivo_id', $periodo_lectivo_id)
                            ->select(
                                        'sga_periodos.id',
                                        'sga_periodos.periodo_lectivo_id',
                                        'sga_periodos.descripcion',
                                        'sga_periodos.numero',
                                        'sga_periodos.periodo_de_promedios',
                                        'sga_periodos.fecha_desde',
                                        'sga_periodos.fecha_hasta')
                            ->orderBy('sga_periodos.numero')
                            ->get();
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
}
