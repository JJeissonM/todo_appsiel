<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\NomContrato;
use App\Nomina\NomDocRegistro;

use DB;
use Input;

class NomDocEncabezadoOt extends NomDocEncabezado
{
    protected $table = 'nom_doc_encabezados';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'descripcion', 'tiempo_a_liquidar', 'total_devengos', 'total_deducciones', 'estado', 'creado_por', 'modificado_por', 'tipo_liquidacion'];

    public static function opciones_campo_select()
    {
        $opciones = NomDocEncabezado::where([
                                                ['estado', '=', 'Activo'],
                                                ['tiempo_a_liquidar', '=', '9999']
                                            ])
                                    ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
