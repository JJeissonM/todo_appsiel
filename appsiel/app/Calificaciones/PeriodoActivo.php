<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use App\Core\Colegio;

use Illuminate\Support\Facades\Auth;

class PeriodoActivo extends Model
{
    protected $table='sga_periodos';

    protected $fillable = ['periodo_lectivo_id','id_colegio','numero', 'descripcion','fecha_desde','fecha_hasta', 'periodo_de_promedios', 'estado', 'cerrado'];

    public static function opciones_campo_select()
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $opciones = Periodo::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_periodos.periodo_lectivo_id')
                            ->where('sga_periodos.id_colegio',$colegio->id)
                            ->where('sga_periodos.estado','Activo')
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
}
