<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class CatalogoObservacionesEvaluacionAspecto extends Model
{
    protected $table = 'sga_catalogo_observaciones_evaluacion_por_aspectos';

    // convencion_valoracion_id es el resultado del calculo, según todas las convencion_valoracion_id ingresadas en cada resultado de los items de aspectos valorados
    protected $fillable = [ 'observacion' ];

    public static function opciones_campo_select()
    {

        $opciones = CatalogoObservacionesEvaluacionAspecto::get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->observacion;
        }

        return $vec;
    }
    
}
