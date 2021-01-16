<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Auth;
use DB;
use View;
use Lava;
use Input;
use NumerosEnLetras;
use Form;
use Storage;

// Modelos
use App\Core\Tercero;

use App\Tesoreria\TesoCuentaBancaria;

use App\CxC\CxcMovimiento;
use App\CxC\CxcDocEncabezado;
use App\CxC\CxcDocRegistro;
use App\CxC\CxcServicio;
use App\CxC\CxcEstadoCartera;

use App\Contabilidad\ContabMovimiento;
use App\PropiedadHorizontal\Propiedad;



class EmailController extends Controller
{
  protected $datos = [];
  protected $encabezado_doc;
  protected $inmueble;
  protected $tercero;
  protected $id_transaccion = 15;// 15 = Cuenta de cobro

  public function __construct()
  {
      $this->middleware('auth');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    //
  }

  /*
    ** Enviar por email un documento
  */
  public static function enviar_por_email_documento( $nombre_remitente, $email_destino, $asunto, $cuerpo_mensaje, $documento_vista )
  {

    $tam_hoja = 'Letter';
    $orientacion='portrait';
    $pdf = \App::make('dompdf.wrapper');
    $pdf->loadHTML( $documento_vista )->setPaper($tam_hoja, $orientacion);

    $nombrearchivo = uniqid().'.pdf';

    // Se almacena el archivo en el dico duro
    Storage::put('pdf_email/'.$nombrearchivo, $pdf->output());
    
    $tipo_mensaje = 'flash_message';
    $texto_mensaje = 'Correo enviado correctamente.';

    if ( !EmailController::enviar_email( $nombre_remitente, $email_destino, $asunto, $cuerpo_mensaje, $nombrearchivo) )
    {
      $tipo_mensaje = 'mensaje_error';
      $texto_mensaje = 'Correo no pudo ser enviado. Verifique la dirección de email del destinatario e intente nuevamente.';      
    }

    return [ 'tipo_mensaje' => $tipo_mensaje, 'texto_mensaje' => $texto_mensaje];

  }

  public function enviar_email_lote( $empresa_id, $core_tipo_doc_app_id, $consec_desde, $consec_hasta )
  {
      $view_pdf = '';
      $vista = 'imprimir';

      $tabla = '<table class="table table-bordered table-striped" id="myTable">
                    <thead>
                      <tr>
                          <th>
                             Inmueble
                          </th>
                          <th>
                             Propietario
                          </th>
                          <th>
                             E-mail
                          </th>
                          <th>
                             Estado envío
                          </th>
                      </tr>
                    </thead>
                    <tbody>';

      for($consecutivo=$consec_desde;$consecutivo<=$consec_hasta;$consecutivo++)
      {
          // A través del tipo de documento y consecutivo se obtiene el ID del encabezado del documento
        $doc_cxc_encabezado = CxcDocEncabezado::where('core_empresa_id', $empresa_id)->where('core_tipo_doc_app_id', $core_tipo_doc_app_id)->where('consecutivo', $consecutivo)->get()[0];

        $view_pdf = $this->vista_preliminar_cxc($doc_cxc_encabezado->id,'imprimir');

        $tam_hoja = 'Letter';
        $orientacion='portrait';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);

        $nombrearchivo = 'cuenta_de_cobro_'.$doc_cxc_encabezado->id.'.pdf';

        Storage::put('pdf_email/'.$nombrearchivo, $pdf->output());

        //$inmueble = Propiedad::find($doc_cxc_encabezado->codigo_referencia_tercero);

        $tercero = Tercero::find($doc_cxc_encabezado->core_tercero_id);

        if ( $this->enviar_email($doc_cxc_encabezado->id, $nombrearchivo) ) 
        {
          $estado_envio = '<span>Enviado</span>';
        }else{
          $estado_envio = '<span style="background: #FD845D;">NO Enviado</span>';
        }
        
        $tabla .= '<tr>
                    <td>'.$this->inmueble->codigo.'</td>
                    <td>'.$tercero->descripcion.'</td>
                    <td>'.$this->inmueble->email.'</td>
                    <td>'.$estado_envio.'</td>
                    </tr>';

      }

      $tabla .= '</tbody></table>';
      
      //echo $tabla;
      return redirect()->to( app('url')->previous() )->with('flash_message','Email(s) enviado(s) correctamente.');
  }

  public static function enviar_email( $nombre_remitente, $email_destino, $asunto, $cuerpo_mensaje, $nombrearchivo = null)
  {

    // Email interno. Debe estar creado en Hostinger
    //$email_interno = 'info@'.substr( url('/'), 7);
    $email_interno = 'info@appsiel.com.co';//.substr( url('/'), 7);
    
    $from = $nombre_remitente." <".$email_interno."> \r\n";
    $headers = "From:" . $from." \r\n";
    $to = $email_destino;

    $subject = $asunto;

    //headers for attachment
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"=A=G=R=O=\"\r\n\r\n";
  
    
    // Armando mensaje del email
    $message = "--=A=G=R=O=\r\n";
    $message .= "Content-type:text/html; charset=utf-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $cuerpo_mensaje . "\r\n\r\n";

    //attachment file path
    if ($nombrearchivo != null) 
    {
      //$nombrearchivo = 'cuenta_de_cobro_'.$id_doc_cxc.'.pdf';
      $url = Storage::getAdapter()->applyPathPrefix('pdf_email/'.$nombrearchivo);
      $file = chunk_split(base64_encode(file_get_contents( $url )));
      
      $message .= "--=A=G=R=O=\r\n";
      $message .= "Content-Type: application/octet-stream; name=\"" . $nombrearchivo . "\"\r\n";
      $message .= "Content-Transfer-Encoding: base64\r\n";
      $message .= "Content-Disposition: attachment; filename=\"" . $nombrearchivo . "\"\r\n\r\n";
      $message .= $file . "\r\n\r\n";
      $message .= "--=A=G=R=O=--";
    }

    return mail($to,$subject,$message, $headers);
  }
}