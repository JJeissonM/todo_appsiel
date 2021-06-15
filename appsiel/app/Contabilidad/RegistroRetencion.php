<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use App\Contabilidad\Retencion;
use App\Contabilidad\ContabMovimiento;

class RegistroRetencion extends Model
{
    protected $table = 'contab_registros_retenciones';

    // tipo = { sufrida | practicada }
    protected $fillable = [ 'tipo', 'numero_certificado', 'fecha_certificado', 'fecha_recepcion_certificado', 'numero_doc_identidad_agente_retencion', 'razon_social_agente_retencion', 'contab_retencion_id', 'valor_base_retencion', 'tasa_retencion', 'valor', 'detalle', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'creado_por', 'modificado_por', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Núm. Certificado', 'F. certificado', 'F. recepción cert.', 'Doc. ID Tercero', 'Tercero', 'Retención', 'Base Ret.', 'Tasa', 'Valor', 'Doc. relacionado', 'Estado'];

    public function retencion()
    {
        return $this->belongsTo(Retencion::class, 'contab_retencion_id' );
    }

    public function almacenar_nuevos_registros( $json_lineas_registros, $doc_encabezado, $valor_base_retencion, $tipo )
    {        
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $valor_retencion = (float)$lineas_registros[$i]->valor_retencion;
            $tasa_retencion = round( $valor_retencion * 100 / $valor_base_retencion, 2);
            $datos = [
                        'tipo' => $tipo,
                        'numero_certificado' => $lineas_registros[$i]->numero_certificado,
                        'fecha_certificado' => $lineas_registros[$i]->fecha_certificado,
                        'fecha_recepcion_certificado' => $lineas_registros[$i]->fecha_recepcion_certificado,
                        'numero_doc_identidad_agente_retencion' => $lineas_registros[$i]->numero_doc_identidad_agente_retencion,
                        'contab_retencion_id' => (int)$lineas_registros[$i]->contab_retencion_id,
                        'valor_base_retencion' => $valor_base_retencion,
                        'tasa_retencion' => $tasa_retencion,
                        'valor' => $valor_retencion,
                        'detalle' => 'Recaudo de CxC'
                    ] + $doc_encabezado->toArray();
            
            RegistroRetencion::create( $datos );

            // Contabilizar Retención
            $datos['tipo_transaccion'] = '';
            $retencion = Retencion::find( (int)$lineas_registros[$i]->contab_retencion_id );
            $movimiento_contable = new ContabMovimiento();
            switch ( $tipo )
            {
                case 'practicada':
                    $movimiento_contable->contabilizar_linea_registro( $datos, $retencion->cta_compras_id, 'Retención ' . $tipo, 0, $valor_retencion );
                    break;
                case 'sufrida':
                    $movimiento_contable->contabilizar_linea_registro( $datos, $retencion->cta_ventas_id, 'Retención ' . $tipo, $valor_retencion, 0 );
                    break;
                
                default:
                    # code...
                    break;
            }                
        }
    }

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
