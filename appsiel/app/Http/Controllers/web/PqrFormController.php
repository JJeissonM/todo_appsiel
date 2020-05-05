<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use View;
use Storage;

use App\Core\Empresa;

use App\web\PqrForm;


class PqrFormController extends Controller
{
    
    public function store(Request $request)
    {
        $registro = PqrForm::create( $request->all() );

        return redirect( 'seccion/' . $request->widget_id . '?id=' . $request->url_id)->with('flash_message', 'Sección almacenada correctamente.');
    }

    public function update(Request $request, $id)
    {
        if ( $id == 'enviar' )
        {
            return $this->enviar_formulario( $request );
        }

        $registro = PqrForm::find( $id );
        $registro->fill( $request->all() );
        $registro->save();

        return redirect( 'seccion/' . $request->widget_id . '?id=' . $request->url_id)->with('flash_message', 'Sección actualizada correctamente.');

    }



    public function enviar_formulario( Request $request )
    {

        $empresa = Empresa::find(1);

        // Email interno. Debe estar creado en Hostinger
        $email_interno = 'info@'.substr( url('/'), 7);

        // Datos requeridos por hostinger
        $from = "Pagina Web <".$email_interno."> \r\n";
        $headers = "From:" . $from." \r\n";
        $to = $request->parametros;
        $subject = "Pagina Web: ".$request->asunto;

        // El mensaje
        $cuerpo_mensaje = View::make('web.formularios.cuerpo_mensaje',compact('request','empresa'))->render();

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"=A=G=R=O=\"\r\n\r\n";


            
        $message = "--=A=G=R=O=\r\n";

        $archivos_enviados = $request->file();

        if( empty( $archivos_enviados ) )
        {
            // Armando mensaje del email
            $message .= "Content-type:text/html; charset=utf-8\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $cuerpo_mensaje . "\r\n\r\n";

        }else{

            foreach ($archivos_enviados as $key => $value)
            {

                // ALMACENAR TEMPORALMENTE EL ARCHIVO EN DISCO
                $archivo = $request->file($key);
                $extension =  $archivo->clientExtension();

                $nombrearchivo = str_slug($archivo->getClientOriginalName()) . '-' . uniqid() . '.' . $extension;

                // Guardar en disco
                Storage::put( 'pdf_email/' . $nombrearchivo, file_get_contents($archivo->getRealPath()));



                // LEER EL ARCHIVO Y ADJUNTARLO AL CUERPO DEL MENSAJE

                $url = Storage::getAdapter()->applyPathPrefix('pdf_email/'.$nombrearchivo);
                $file = chunk_split(base64_encode(file_get_contents( $url )));

                $message .= "Content-Type: application/octet-stream; name=\"" . $nombrearchivo . "\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n";
                $message .= "Content-Disposition: attachment; filename=\"" . $nombrearchivo . "\"\r\n\r\n";
                $message .= $file . "\r\n\r\n";
                $message .= "--=A=G=R=O=--";
            }                
        }

        //if(true)
        if (mail($to,$subject,$message, $headers))
        {
            $tipo_msj = 'flash_message';
            $alerta = '<strong>¡El mensaje se ha enviado correctamente!</strong> Nos comunicaremos con usted los más pronto posible.';
        } else {
            $tipo_msj = 'mensaje_error';
            $alerta = '<strong>¡El mensaje no pudo ser enviado!</strong> Por favor intente nuevamente. Si el problema persiste, intente más tarde.';
        }

        return redirect( $request->pagina_slug )->with( $tipo_msj, $alerta);
    }

}
