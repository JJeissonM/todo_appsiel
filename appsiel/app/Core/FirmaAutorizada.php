<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class FirmaAutorizada extends Model
{
    protected $table = 'core_firmas_autorizadas'; 

    protected $fillable = ['core_empresa_id','core_tercero_id','titulo_tercero','estado'];

    public $encabezado_tabla = ['Tercero','Título/Cargo','Estado','Acción'];

    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';

        $registros = FirmaAutorizada::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_firmas_autorizadas.core_tercero_id')
                    ->where('core_firmas_autorizadas.core_empresa_id',Auth::user()->empresa_id)
                    ->select(DB::raw($select_raw),'core_firmas_autorizadas.titulo_tercero AS campo2','core_firmas_autorizadas.estado AS campo3','core_firmas_autorizadas.id AS campo4')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_datos($id)
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.apellido1," ",core_terceros.apellido2) AS nombre';

        $registros = FirmaAutorizada::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_firmas_autorizadas.core_tercero_id')
                    ->where('core_firmas_autorizadas.id',$id)
                    ->select(DB::raw($select_raw),'core_firmas_autorizadas.titulo_tercero','core_terceros.numero_identificacion')
                    ->get()
                    ->toArray();

        return $registros[0];
    }

    public static function get_firma_tercero( $core_tercero_id )
    {
        return FirmaAutorizada::where( 'core_tercero_id', $core_tercero_id)->get()->first();
    }
}
