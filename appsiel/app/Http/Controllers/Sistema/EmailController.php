<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;


// Modelos
use App\Core\Tercero;

use App\CxC\CxcDocEncabezado;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

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
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML( $documento_vista )->setPaper($tam_hoja, $orientacion);

    $nombrearchivo = uniqid().'.pdf';

    // Se almacena el archivo en el dico duro
    Storage::put('pdf_email/'.$nombrearchivo, $pdf->output());
    
    $tipo_mensaje = 'flash_message';
    $texto_mensaje = 'Correo enviado correctamente.';
    
    if ( gettype( filter_var($email_destino, FILTER_VALIDATE_EMAIL) ) != 'string' )
    {
      $tipo_mensaje = 'mensaje_error';
      $texto_mensaje = 'Correo no pudo ser enviado. Formato de Email del destinatario incorrecto: ' . $email_destino; 
    }elseif ( !EmailController::enviar_email( $nombre_remitente, $email_destino, $asunto, $cuerpo_mensaje, $nombrearchivo) )
    {
      $tipo_mensaje = 'mensaje_error';
      $texto_mensaje = 'Correo no pudo ser enviado. Verifique la dirección de email del destinatario e intente nuevamente.';      
    }

    return [ 'tipo_mensaje' => $tipo_mensaje, 'texto_mensaje' => $texto_mensaje];

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