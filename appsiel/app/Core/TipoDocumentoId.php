<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoId extends Model
{
    protected $table = 'core_tipos_docs_id'; 

    protected $fillable = ['descripcion','abreviatura','codigo_sire'];

    public static function opciones_campo_select()
    {
        $opciones = TipoDocumentoId::orderBy('descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
