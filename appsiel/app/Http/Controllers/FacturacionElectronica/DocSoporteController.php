<?php

namespace App\Http\Controllers\FacturacionElectronica;

use App\Http\Controllers\Core\TransaccionController;

use App\FacturacionElectronica\DocSoporte;
use App\FacturacionElectronica\NotaAjusteDocSoporte;
use App\FacturacionElectronica\OSEI\NotaAjusteDocSoporte as OSEINotaAjusteDocSoporte;
use Illuminate\Support\Facades\Input;

class DocSoporteController extends TransaccionController
{
    // Llamado directamente
    public function enviar_doc_soporte( $id )
    {
        $encabezado_factura = DocSoporte::find( $id );

        if ( empty( $encabezado_factura->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
            return redirect( 'compras/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( 'mensaje_error', 'Documento no puede ser enviado. El prefijo ' . $encabezado_factura->tipo_documento_app->prefijo . ' no tiene una resolución asociada.');
        }

        $validation = $encabezado_factura->validate_customer_data();
        if ( $validation->error )
        {
            return redirect( 'compras/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( 'mensaje_error', 'Documento no puede ser enviado. Presenta errores en los datos del proveedor: ' . $validation->message);
        }        

        $mensaje = $encabezado_factura->enviar_al_proveedor_tecnologico();                

        if ( $mensaje->tipo != 'mensaje_error' )
        {            
            $encabezado_factura->estado = 'Enviado';
            $encabezado_factura->save();
        }

        return redirect( 'compras/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
    }    

    // Nota de ajuste al documento soporte (transacción 62)
    public function enviar_nota_ajuste( $id )
    {
        $nota = NotaAjusteDocSoporte::find( $id );

        $url_nota = 'compras_notas_ajuste_doc_soporte/' . $nota->id . '?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion');

        if ( $nota->estado == 'Anulado' || $nota->enviado_electronicamente() )
        {
            return redirect( $url_nota )->with( 'mensaje_error', 'La nota de ajuste está anulada o ya fue enviada.');
        }

        // La validación del proveedor vive en DocSoporte (mismo encabezado de compras).
        $validation = DocSoporte::find( $nota->id )->validate_customer_data();
        if ( $validation->error )
        {
            return redirect( $url_nota )->with( 'mensaje_error', 'Documento no puede ser enviado. Presenta errores en los datos del proveedor: ' . $validation->message);
        }

        $mensaje = (new OSEINotaAjusteDocSoporte( $nota, 'support_doc_note' ))->procesar_envio_factura();

        if ( $mensaje->tipo != 'mensaje_error' )
        {
            $nota->estado = 'Enviado';
            $nota->save();
        }

        return redirect( $url_nota )->with( $mensaje->tipo, $mensaje->contenido);
    }
}
