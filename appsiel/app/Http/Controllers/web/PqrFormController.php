<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use View;
use Storage;

use App\Core\Empresa;
use App\Sistema\Campo;

use App\web\PqrForm;


class PqrFormController extends Controller
{

    public function store(Request $request)
    {
        if ($request->tipo_fondo == '') {
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('mensaje_error', 'Debe indicar el tipo de fondo a usar en el componente.');
        }
        $registro = PqrForm::create($request->all());

        if ($request->tipo_fondo == 'IMAGEN') {
            //el fondo es una imagen
            $file = $request->file('fondo');
            $name = time() . $file->getClientOriginalName();
            $filename = "img/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $registro->fondo = $filename;
            } else {
                $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error', $message);
            }
        }
        
        return redirect('seccion/' . $request->widget_id . '?id=' . $request->url_id)->with('flash_message', 'Sección almacenada correctamente.');
    }

    public function update(Request $request, $id)
    {
        if ($id == 'enviar') {
            return $this->enviar_formulario($request);
        }

        //dd($request);
        $registro = PqrForm::find($id);
        $tipo_fondo = $registro->tipo_fondo;
        $registro->campos_mostrar = $request->campos_mostrar;
        $registro->contenido_encabezado = $request->contenido_encabezado;
        $registro->contenido_pie_formulario = $request->contenido_pie_formulario;
        $registro->parametros = $request->parametros;
        $registro->configuracionfuente_id = $request->configuracionfuente_id;

        if ($request->tipo_fondo == '') {
            $registro->tipo_fondo = $tipo_fondo;
        }
        if ($request->tipo_fondo != '') {
            if ($request->tipo_fondo == 'IMAGEN') {
                if (isset($request->fondo)) {
                    //el fondo es una imagen
                    $file = $request->file('fondo');
                    $name = time() . $file->getClientOriginalName();
                    $filename = "img/" . $name;
                    $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
                    if ($flag !== false) {
                        $registro->fondo = $filename;
                        $registro->tipo_fondo = 'IMAGEN';
                        $registro->repetir = $request->repetir;
                        $registro->direccion = $request->direccion;
                    } else {
                        $message = 'Error inesperado al intentar guardar la imagen de fondo, por favor intente nuevamente mas tarde';
                        return redirect()->back()->withInput($request->input())
                            ->with('mensaje_error', $message);
                    }
                }
            } else {
                $registro->fondo = $request->fondo;
                $registro->tipo_fondo = "COLOR";
            }
        }
        //dd($registro);
        $result = $registro->save();
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'La configuración de la sección fue modificada correctamente.';
            return redirect('seccion/' . $request->widget_id . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser modificada, intente mas tarde.';
            return redirect('seccion/' . $request->widget_id . $variables_url)->with('flash_message', $message);
        }

        //return redirect('seccion/' . $request->widget_id . '?id=' . $request->url_id)->with('flash_message', 'Sección actualizada correctamente.');
    }



    public function enviar_formulario(Request $request)
    {
        $empresa = Empresa::find(1);

        // Email interno. Debe estar creado en Hostinger
        $email_interno = 'info@' . substr(url('/'), 7);

        // Datos requeridos por hostinger
        $from = "Pagina Web <" . $email_interno . "> \r\n";
        $headers = "From:" . $from . " \r\n";
        $to = $request->email_recepcion;
        $subject = "Comentario desde página Web";


        // El mensaje
        $formulario = PqrForm::where('widget_id', $request->widget_id)->get()->first();
        $campos_mostrar = json_decode($formulario->campos_mostrar);
        $campos = [];
        foreach ($campos_mostrar as $key => $value) {
            $el_campo = Campo::find($key);
            $variable = $el_campo->name;
            $campos[] = [$el_campo->descripcion, $request->$variable];
        }
        $cuerpo_mensaje = View::make('web.formularios.cuerpo_mensaje', compact('request', 'empresa', 'campos'))->render();


        // Encabezados
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"=A=G=R=O=\"\r\n\r\n";

        $message = "--=A=G=R=O=\r\n";
        $message .= "Content-type:text/html; charset=utf-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $cuerpo_mensaje . "\r\n\r\n";


        // Manjeo de archivos adjuntos
        $archivos_enviados = $request->file();

        if (!empty($archivos_enviados)) {
            foreach ($archivos_enviados as $key => $value) {

                // ALMACENAR TEMPORALMENTE EL ARCHIVO EN DISCO
                $archivo = $request->file($key);
                $extension =  $archivo->clientExtension();

                $nombrearchivo = str_slug($archivo->getClientOriginalName()) . '-' . uniqid() . '.' . $extension;

                // Guardar en disco
                Storage::put('pdf_email/' . $nombrearchivo, file_get_contents($archivo->getRealPath()));



                // LEER EL ARCHIVO Y ADJUNTARLO AL CUERPO DEL MENSAJE

                $url = Storage::getAdapter()->applyPathPrefix('pdf_email/' . $nombrearchivo);
                $file = chunk_split(base64_encode(file_get_contents($url)));

                $message .= "--=A=G=R=O=\r\n";
                $message .= "Content-Type: application/octet-stream; name=\"" . $nombrearchivo . "\"\r\n";
                $message .= "Content-Transfer-Encoding: base64\r\n";
                $message .= "Content-Disposition: attachment; filename=\"" . $nombrearchivo . "\"\r\n\r\n";
                $message .= $file . "\r\n\r\n";
                $message .= "--=A=G=R=O=--";
            }
        }

        //dd( [ $to, $subject, $message, $headers ] );

        //if(true)
        if (mail($to, $subject, $message, $headers)) {
            $tipo_msj = 'flash_message';
            $alerta = '<strong>¡El mensaje se ha enviado correctamente!</strong> Nos comunicaremos con usted los más pronto posible.';
        } else {
            $tipo_msj = 'mensaje_error';
            $alerta = '<strong>¡El mensaje no pudo ser enviado!</strong> Por favor intente nuevamente. Si el problema persiste, intente más tarde.';
        }

        return redirect($request->pagina_slug)->with($tipo_msj, $alerta);
    }
}
