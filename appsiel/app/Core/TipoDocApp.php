<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use App\Core\ConsecutivoDocumento; 

class TipoDocApp extends Model
{
    protected $table = 'core_tipos_docs_apps'; 

    protected $fillable = ['prefijo','descripcion','estado'];

    public $encabezado_tabla = ['ID','Prefijo','Descripción','Estado','Acción'];

    public function resolucion_facturacion()
    {
        return $this->hasMany( 'App\Ventas\ResolucionFacturacion', 'tipo_doc_app_id');
    }

    public static function consultar_registros()
    {
    	$registros = TipoDocApp::select('core_tipos_docs_apps.id AS campo1','core_tipos_docs_apps.prefijo AS campo2','core_tipos_docs_apps.descripcion AS campo3','core_tipos_docs_apps.estado AS campo4','core_tipos_docs_apps.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = TipoDocApp::select('core_tipos_docs_apps.id','core_tipos_docs_apps.descripcion','core_tipos_docs_apps.prefijo')
                    ->orderBy('descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->prefijo.' '.$opcion->descripcion.' ('.$opcion->id.')';
        }

        return $vec;
    }

    public static function get_consecutivo_actual( $core_empresa_id, $tipo_doc_app_id )
    {
        
        $consecutivo = ConsecutivoDocumento::where('core_empresa_id',$core_empresa_id)
                                    ->where('core_documento_app_id',$tipo_doc_app_id)
                                    ->get()
                                    ->first();

        // Si no hay un consecutivo creado para es tipo de documento en esa empresa, se crea el registro en la BD
        if ( is_null($consecutivo) )
        {
            $consecutivo = new ConsecutivoDocumento;
            $consecutivo->core_empresa_id = $core_empresa_id;
            $consecutivo->core_documento_app_id = $tipo_doc_app_id;
            $consecutivo->consecutivo_actual = 0;
            $consecutivo->save();
            $consecutivo = 0;
        }else{
            $consecutivo = $consecutivo->consecutivo_actual;
        }

        return $consecutivo;
    }

    /*
        Esta función es llamada inmediatamente despues de get_consecutivo_actual
        Warning: es posible que haya conclifcto cuando varios usuariios estan haciendo documentos? que uno llame get_consecutivo_actual y otros usuario aumente el consecutivo ante de que lo haga el primer usuario?  
    */
    public static function aumentar_consecutivo( $core_empresa_id, $tipo_doc_app_id )
    {
        ConsecutivoDocumento::where('core_empresa_id',$core_empresa_id)
                            ->where('core_documento_app_id',$tipo_doc_app_id)
                            ->increment('consecutivo_actual');
    }
}
