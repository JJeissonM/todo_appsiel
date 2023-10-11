<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;
use Exception;
use Illuminate\Contracts\Mail\Mailer;
// Modelos
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

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
    $from = (object)[
      'email' => env('MAIL_USERNAME'),
      'name' => $nombre_remitente
    ];

    $to = (object)[
      'email' => $email_destino,
      'name' => null
    ];

    try {
      Mail::send('layouts.cuerpo_email', ['cuerpo_mensaje' => $cuerpo_mensaje], function ($m) use ($from, $to, $asunto, $nombrearchivo) {
          $m->from($from->email, $from->name);
          $m->to($to->email, $to->name)->subject($asunto);

          if ($nombrearchivo != null) 
          {
            //$nombrearchivo = 'cuenta_de_cobro_'.$id_doc_cxc.'.pdf';
            $url = Storage::getAdapter()->applyPathPrefix('pdf_email/'.$nombrearchivo);
            $m->attach($url);
          }          
      });
    
      return true;
    } catch (Exception $ex) {
        // Debug via $ex->getMessage();
      dd('Fallo de envío',$ex->getMessage());
      return false;
    }
  }

  public function test_email()
  {    
    $doc_encabezado_id = 34;
    $empleado_id = 2;
    $documento = NomDocEncabezado::find( $doc_encabezado_id );

    $empleado = NomContrato::find( $empleado_id );
    
    $enviado = 'false-';
    $vista = View::make('nomina.reportes.tabla_desprendibles_pagos', compact('documento', 'empleado') )->render();

    $tercero = $empleado->tercero;
    if ( $tercero->email != '' )
    {
        $asunto = 'Desprendible de pago de nómina. '.$documento->descripcion;

        $cuerpo_mensaje = 'Hola ' . $tercero->nombre1 . ' ' .  $tercero->otros_nombres . ', <br> Le hacemos llegar su volante de nómina. <br><br> <b>Documento:</b> '. $documento->descripcion . ' <br> <b>Fecha:</b> ' . $documento->fecha . ' <br> Cualquier duda o inquietud, favor remitirla al área de talento humano. <br><br> Atentamente, <br><br> ANALISTA DE NÓMINA <br> ' . $documento->empresa->descripcion . ' <br> Tel. ' . $documento->empresa->telefono1 . ' <br> Email: ' . $documento->empresa->email;

        $vec = EmailController::enviar_por_email_documento( $documento->empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $vista );                

        if ( $vec['tipo_mensaje'] == 'flash_message' )
        {
            $enviado = 'true-';
        }
    }

    return $enviado . $empleado->tercero->descripcion;
  }
}