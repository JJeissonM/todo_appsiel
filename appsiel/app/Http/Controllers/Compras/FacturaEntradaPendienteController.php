<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Core\EncabezadoDocumentoTransaccion;
use App\Inventarios\InvDocRegistro;
use Auth;
use View;
use Input;
use Form;

use App\Tesoreria\RegistrosMediosPago;

class FacturaEntradaPendienteController extends CompraController
{
    protected $doc_encabezado;
    protected $empresa, $app, $modelo, $transaccion, $variables_url;
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store( Request $request )
    {
        $this->validar_cuenta_por_pagar_directa($request);
        $this->validate($request, ['reteica_retencion_id' => 'integer|min:0']);
        try {
            (new \App\Compras\Services\ReteicaService())->validar_seleccion(
                $request->input('reteica_retencion_id', 0), $request->core_tipo_transaccion_id
            );
        } catch (\InvalidArgumentException $e) {
            return $this->respuesta_error_guardado($request, $e->getMessage());
        }
        $doc_encabezado = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $datos = $request->all();

            $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
            $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

            $lineas_registros = json_decode( $request->lineas_registros );

            $registros_medio_pago = new RegistrosMediosPago;

            $datos['registros_medio_pago'] = (int)$request->reteica_retencion_id ? [] : $registros_medio_pago->get_datos_ids( $request->all()['lineas_registros_medios_recaudo'], null, self::get_total_documento_desde_lineas_registros_desde_entrada( $doc_encabezado, $lineas_registros ), 'compras' );

            CompraController::crear_lineas_registros_compras( $datos, $doc_encabezado, $lineas_registros );

            (new \App\Compras\Services\ContabilidadService())->aplicar_retenciones_por_linea_compras($doc_encabezado);
            (new \App\Compras\Services\ReteicaService())->contabilizar($doc_encabezado);
            return $doc_encabezado;
        });

        return $this->respuesta_compra_guardada($request, $doc_encabezado);
    }

    
    public static function get_total_documento_desde_lineas_registros_desde_entrada( $doc_encabezado, $lineas_registros )
    {
        $total_documento = 0;
        $liquida_impuestos = (int)config('configuracion.liquidacion_impuestos');
        if (!is_null($doc_encabezado->proveedor)) {
            $liquida_impuestos = $liquida_impuestos && (int)$doc_encabezado->proveedor->liquida_impuestos;
        }
        // Por cada entrada de almacén pendiente
        $cantidad_registros = count( $lineas_registros );

        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_entrada_id = (int)$lineas_registros[$i]->id_doc;

            $registros_entrada = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_entrada_id )->get();

            foreach ($registros_entrada as $un_registro)
            {
                $datos_linea_entrada = CompraController::get_datos_factura_desde_registro_entrada($un_registro, $doc_encabezado->proveedor_id);
                $total_documento += $liquida_impuestos ? $datos_linea_entrada['precio_total'] : $datos_linea_entrada['base_impuesto'];
            } // Fin por cada registro de la entrada
        }

        return $total_documento;
    }

}
