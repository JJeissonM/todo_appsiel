<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TesoCaja extends Model
{

    protected $fillable = ['descripcion','core_empresa_id','controla_usuarios','estado','contab_cuenta_id'];

    public $encabezado_tabla = ['Descripción','Empresa','Controla usuarios','Estado','Acción'];

    public static function consultar_registros()
    {
    	$select_raw = 'CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social) AS campo2';

        $registros = TesoCaja::leftJoin('core_empresas','core_empresas.id','=','teso_cajas.core_empresa_id')
                    ->where('core_empresa_id',Auth::user()->empresa_id)
                    ->select('teso_cajas.descripcion AS campo1',DB::raw($select_raw),'teso_cajas.controla_usuarios AS campo3','teso_cajas.estado AS campo4','teso_cajas.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = TesoCaja::where('teso_cajas.estado','Activo')
                    ->select('teso_cajas.id','teso_cajas.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"contab_movimientos",
                                    "llave_foranea":"teso_caja_id",
                                    "mensaje":"Está en movimientos contables."
                                },
                            "1":{
                                    "tabla":"teso_arqueos_caja",
                                    "llave_foranea":"teso_caja_id",
                                    "mensaje":"Está en arqueos de caja."
                                },
                            "2":{
                                    "tabla":"teso_doc_encabezados",
                                    "llave_foranea":"teso_caja_id",
                                    "mensaje":"Está en registros de documentos de tesorería."
                                },
                            "3":{
                                    "tabla":"teso_doc_registros",
                                    "llave_foranea":"teso_caja_id",
                                    "mensaje":"Está en movimientos de tesorería."
                                },
                            "4":{
                                    "tabla":"teso_movimientos",
                                    "llave_foranea":"teso_caja_id",
                                    "mensaje":"Está en movimientos de tesorería."
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
