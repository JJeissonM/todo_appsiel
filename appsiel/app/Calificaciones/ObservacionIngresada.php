<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ObservacionIngresada extends Model
{
    protected $table = 'sga_observaciones_ingresadas';

    protected $fillable = ['id_colegio','id_periodo','curso_id'];

    public static function cantidad_x_periodo_curso( $periodo_id, $curso_id )
    {
        return ObservacionIngresada::where(
                                            [
                                                'id_periodo' => $periodo_id,
                                                'curso_id' => $curso_id
                                            ]
                                        )
                                    ->count();
    }
}
