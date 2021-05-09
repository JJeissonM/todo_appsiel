<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

class RegistroRetencion extends Model
{
    protected $table = 'contab_registros_retenciones';
    
    protected $fillable = ['tipo', 'numero_certificado', 'fecha_certificado', 'fecha_recepcion_certificado', 'numero_doc_identidad_agente_retencion', 'razon_social_agente_retencion', 'contab_retencion_id', 'valor_base_retencion', 'tasa_retencion', 'valor', 'detalle', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'creado_por', 'modificado_por', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Núm. Certificado', 'F. certificado', 'F. recepción cert.', 'Doc. ID Tercero', 'Tercero', 'Retención', 'Base Ret.', 'Tasa', 'Valor', 'Doc. relacionado', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        return RegistroRetencion::select('contab_registros_retenciones.numero_certificado AS campo1', 'contab_registros_retenciones.fecha_certificado AS campo2', 'contab_registros_retenciones.fecha_recepcion_certificado AS campo3', 'contab_registros_retenciones.numero_doc_identidad_agente_retencion AS campo4', 'contab_registros_retenciones.razon_social_agente_retencion AS campo5', 'contab_registros_retenciones.contab_retencion_id AS campo6', 'contab_registros_retenciones.valor_base_retencion AS campo7', 'contab_registros_retenciones.tasa_retencion AS campo8', 'contab_registros_retenciones.valor AS campo9', 'contab_registros_retenciones.core_tipo_transaccion_id AS campo10', 'contab_registros_retenciones.estado AS campo11', 'contab_registros_retenciones.id AS campo12')
        ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $string = RegistroRetencion::select('contab_registros_retenciones.numero_certificado AS campo1', 'contab_registros_retenciones.fecha_certificado AS campo2', 'contab_registros_retenciones.fecha_recepcion_certificado AS campo3', 'contab_registros_retenciones.numero_doc_identidad_agente_retencion AS campo4', 'contab_registros_retenciones.razon_social_agente_retencion AS campo5', 'contab_registros_retenciones.contab_retencion_id AS campo6', 'contab_registros_retenciones.valor_base_retencion AS campo7', 'contab_registros_retenciones.tasa_retencion AS campo8', 'contab_registros_retenciones.valor AS campo9', 'contab_registros_retenciones.core_tipo_transaccion_id AS campo10', 'contab_registros_retenciones.estado AS campo11', 'contab_registros_retenciones.id AS campo12')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE REGISTROS DE RETENCIONES";
    }

    public static function opciones_campo_select()
    {
        $opciones = RegistroRetencion::where('contab_registros_retenciones.estado','Activo')
                    ->select('contab_registros_retenciones.id','contab_registros_retenciones.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
