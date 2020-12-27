<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class ClaseRiesgoLaboral extends Model
{
    protected $table = 'nom_clases_riesgos_laborales';
    protected $fillable = ['descripcion', 'detalle', 'porcentaje_liquidacion', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Clase riesgo', 'Detalle', 'Procentaje liquidación', 'Estado'];
    public static function consultar_registros($nro_registros)
    {
        return ClaseRiesgoLaboral::select('nom_clases_riesgos_laborales.descripcion AS campo1', 'nom_clases_riesgos_laborales.detalle AS campo2', 'nom_clases_riesgos_laborales.porcentaje_liquidacion AS campo3', 'nom_clases_riesgos_laborales.estado AS campo4', 'nom_clases_riesgos_laborales.id AS campo5')
            ->orderBy('nom_clases_riesgos_laborales.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function opciones_campo_select()
    {
        $opciones = ClaseRiesgoLaboral::where('nom_clases_riesgos_laborales.estado', 'Activo')
            ->select('nom_clases_riesgos_laborales.id', 'nom_clases_riesgos_laborales.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
