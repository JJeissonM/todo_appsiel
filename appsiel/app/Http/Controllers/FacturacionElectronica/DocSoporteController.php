<?php

namespace App\Http\Controllers\FacturacionElectronica;

use App\Http\Controllers\Core\TransaccionController;

use App\FacturacionElectronica\DocSoporte;
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
}
