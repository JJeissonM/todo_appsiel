<?php 

namespace App\VentasPos\Services;

use App\CxC\CxcMovimiento;
use App\Http\Controllers\CxC\DocCruceController;
use Illuminate\Http\Request;

class CxCService
{
    /**
     * Crea un cruce de documentos CxC con anticipos.
     */
    public function crear_cruce_con_anticipos( $doc_encabezado, $object_anticipos)
    {
        (new DocCruceController())->store( $this->build_request_object($doc_encabezado, $object_anticipos) );
    }

    /**
     * Construye el objeto de solicitud para crear un cruce de documentos CxC.
     */
    public function build_request_object( $doc_encabezado, $object_anticipos )
    {
        $request = new Request();

        $cxc_movimiento_id = CxcMovimiento::where('core_empresa_id', $doc_encabezado->core_empresa_id)
            ->where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
            ->where('consecutivo', $doc_encabezado->consecutivo)
            ->first()->id;

        $valor_aplicar_factura = 0;
        $json_object_anticipos = json_decode( '[' . $object_anticipos . ']', true);
        if (is_array($json_object_anticipos)) {
            foreach ($json_object_anticipos as $un_anticipo) {
                $valor_aplicar_factura += abs( $un_anticipo['valor_aplicar'] );
            }
        }
        
        $object_anticipos .= ',{"cxc_movimiento_id":"' . $cxc_movimiento_id . '","Documento":"--","Fecha":"--","saldo_pendiente":"000","valor_aplicar":"' . $valor_aplicar_factura . '"},{"cxc_movimiento_id":"","Documento":"0","Fecha":"","saldo_pendiente":""}';

        $request["core_empresa_id"] = $doc_encabezado->core_empresa_id;
        $request["core_tipo_doc_app_id"] = config('ventas.cruces_cxc_tipo_doc_app_id');
        $request["fecha"] = $doc_encabezado->fecha;
        $request["core_tercero_id"] = $doc_encabezado->core_tercero_id;
        $request["descripcion"] = "";
        $request["documento_soporte"] = "";
        $request["consecutivo"] = "";
        $request["estado"] = "Activo";
        $request["modificado_por"] = "0";
        $request["creado_por"] = $doc_encabezado->creado_por;
        $request["core_tipo_transaccion_id"] = config('ventas.cruces_cxc_tipo_transaccion_id');
        $request["tabla_documentos_a_cancelar"] = '[' . $object_anticipos . ']';
        $request["url_id"] = "13"; // Ventas
        $request["url_id_modelo"] = config('ventas.cruces_cxc_modelo_id');
        $request["url_id_transaccion"] = config('ventas.cruces_cxc_tipo_transaccion_id');

        return $request;
    }
}