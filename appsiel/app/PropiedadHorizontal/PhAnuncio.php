<?php

namespace App\PropiedadHorizontal;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;


class PhAnuncio extends Model
{
    protected $fillable = ['core_empresa_id','descripcion','detalle','fecha_desde','fecha_hasta','numero_clicks','nivel_visualizacion','enlace_web','enlace_facebook','enlace_instagram','estado'];

    public $encabezado_tabla = ['Título','Detalle','Fecha desde','Fecha hasta','Estado','Acción'];

    public static function consultar_registros()
    {
        $registros = PhAnuncio::where('ph_anuncios.core_empresa_id', Auth::user()->empresa_id)
                    ->select('ph_anuncios.descripcion AS campo1','ph_anuncios.detalle AS campo2','ph_anuncios.fecha_desde AS campo3','ph_anuncios.fecha_hasta AS campo4','ph_anuncios.estado AS campo5','ph_anuncios.id AS campo6')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
