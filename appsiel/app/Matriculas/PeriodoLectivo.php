<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use App\Core\Colegio;
use App\Calificaciones\Periodo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodoLectivo extends Model
{
    protected $table='sga_periodos_lectivos';

    protected $fillable = ['id_colegio','descripcion','fecha_desde','fecha_hasta','estado','cerrado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripcion', 'Fecha desde', 'Fecha hasta', 'Cerrado', 'Estado'];

    public function periodos()
    {
        return $this->hasMany('App\Calificaciones\Periodo', 'periodo_lectivo_id');
    }

    public function matriculas()
    {
        return $this->hasMany('App\Matriculas\Matricula', 'periodo_lectivo_id');
    }

    public function get_anio()
    {
        return explode('-', $this->fecha_desde)[0];
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'IF(sga_periodos_lectivos.cerrado=0,REPLACE(sga_periodos_lectivos.cerrado,0,"No"),REPLACE(sga_periodos_lectivos.cerrado,1,"Si")) AS campo4';

        $registros = PeriodoLectivo::select(
            'sga_periodos_lectivos.descripcion AS campo1',
            'sga_periodos_lectivos.fecha_desde AS campo2',
            'sga_periodos_lectivos.fecha_hasta AS campo3',
            DB::raw($select_raw),
            'sga_periodos_lectivos.estado AS campo5',
            'sga_periodos_lectivos.id AS campo6'
        )->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.fecha_desde", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.fecha_hasta", "LIKE", "%$search%")
            ->orWhere("CERRADO", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.estado", "LIKE", "%$search%")
            ->orderBy('sga_periodos_lectivos.fecha_hasta', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'IF(sga_periodos_lectivos.cerrado=0,REPLACE(sga_periodos_lectivos.cerrado,0,"No"),REPLACE(sga_periodos_lectivos.cerrado,1,"Si")) AS CERRADO';

        $string = PeriodoLectivo::select(
            'sga_periodos_lectivos.descripcion AS DESCRIPCIÓN',
            'sga_periodos_lectivos.fecha_desde AS FECHA DESDE',
            'sga_periodos_lectivos.fecha_hasta AS FECHA HASTA',
            DB::raw($select_raw),
            'sga_periodos_lectivos.estado AS ESTADO'
        )->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.fecha_desde", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.fecha_hasta", "LIKE", "%$search%")
            ->orWhere("CERRADO", "LIKE", "%$search%")
            ->orWhere("sga_periodos_lectivos.estado", "LIKE", "%$search%")
            ->orderBy('sga_periodos_lectivos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PERDIODOS LECTIVO";
    }

    // El archivo js debe estar en la carpeta public
    //public $archivo_js = 'assets/js/calificaciones_periodos.js';

    public static function opciones_campo_select()
    {
        $user = Auth::user();

        $colegio = Colegio::where('empresa_id', $user->empresa_id)->get()[0];
        if ($user->hasRole('Profesor') || $user->hasRole('Director de grupo'))
        {
            $opcion = PeriodoLectivo::where('id_colegio',$colegio->id)
                                ->where('estado','Activo')
                                ->where('cerrado',0)
                                ->orderBy('fecha_desde')
                                ->get()
                                ->first();

            if ($opcion != null) {
                return [
                    $opcion->id => $opcion->descripcion
                ];
            }

            return [];
        }


        $opciones = PeriodoLectivo::where('id_colegio',$colegio->id)
                                ->where('estado','Activo')
                                ->where('cerrado',0)
                                ->orderBy('fecha_desde')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_array_activos()
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $opciones = PeriodoLectivo::where('id_colegio',$colegio->id)
                                ->where('estado','Activo')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_actual()
    {
        $empresa_id = 1;
        $user = Auth::user();
        if ( !is_null($user) )
        {
            $empresa_id = Auth::user()->empresa_id;
        }

        $colegio = Colegio::where('empresa_id', $empresa_id)->get()->first();

        return PeriodoLectivo::where('id_colegio',$colegio->id)
                            ->where('estado','Activo')
                            ->where('cerrado',0)
                            ->orderBy('fecha_desde')
                            ->get()
                            ->last();
    }

    public static function get_anio_actual()
    {
        return explode( '-', PeriodoLectivo::get_actual()->fecha_desde )[0];
    }

    public static function get_segun_periodo( $periodo_id )
    {
        return PeriodoLectivo::find( Periodo::find( $periodo_id )->periodo_lectivo_id );
    }

    public function periodo_final_del_anio_lectivo()
    {
        return Periodo::where( [
                                ['periodo_lectivo_id', '=', $this->id],
                                ['estado', '=', 'Activo']
                            ] )
                        ->orderBy('numero')->get()->last();
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"sga_asignaciones_profesores",
                                    "llave_foranea":"periodo_lectivo_id",
                                    "mensaje":"Ya hay ingresada Carga Académica de profesores en ese año lectivo."
                                },
                            "1":{
                                    "tabla":"sga_curso_tiene_asignaturas",
                                    "llave_foranea":"periodo_lectivo_id",
                                    "mensaje":"Ya hay Asignaturas asignadas a cursos en ese año lectivo."
                                },
                            "2":{
                                    "tabla":"sga_escala_valoracion",
                                    "llave_foranea":"periodo_lectivo_id",
                                    "mensaje":"Ya hay escala de valoración creada en ese año lectivo."
                                },
                            "3":{
                                    "tabla":"sga_matriculas",
                                    "llave_foranea":"periodo_lectivo_id",
                                    "mensaje":"Ya hay matrículas registradas en ese año lectivo."
                                },
                            "4":{
                                    "tabla":"sga_periodos",
                                    "llave_foranea":"periodo_lectivo_id",
                                    "mensaje":"Ya hay periodos creados en ese año lectivo."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    } 
}
