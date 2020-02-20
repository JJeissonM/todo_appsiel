<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;


class Micrositio extends Model
{
    protected $table = 'pw_micrositios';
    
    protected $fillable = ['core_empresa_id','descripcion','directorio_archivos','fecha_desde','fecha_hasta','numero_clicks','nivel_visualizacion','enlace_web','enlace_facebook','enlace_instagram','estado'];

    public $encabezado_tabla = ['Empresa','Título','ID en directorio de archivos','Estado','Acción'];

    public static function consultar_registros()
    {
        $empresa_id = Auth::user()->empresa_id;

        $select_raw = 'CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social) AS campo1';

        $registros = Micrositio::leftJoin('core_empresas','core_empresas.id','=','pw_micrositios.core_empresa_id')
                    ->where('pw_micrositios.core_empresa_id',$empresa_id)
                    ->select(DB::raw($select_raw),'pw_micrositios.descripcion AS campo2','pw_micrositios.directorio_archivos AS campo3','pw_micrositios.estado AS campo4','pw_micrositios.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
