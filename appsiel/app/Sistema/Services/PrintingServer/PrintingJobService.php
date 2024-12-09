<?php

namespace App\Sistema\Services\PrintingServer;

use Exception;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\EscposImage;

class PrintingJobService
{
    public $status = 'Completado';

    public function print_job($data)
    {
        if (!$this->validate_data($data)) {
            return json_encode(
                    [
                        'status' => 'error',
                        'message' => 'Error en el envio de parámetros.'
                    ]
                );
        }
        
        $message = $this->sent_to_print( $data );

        $json_response = $this->get_json_response($message); // This set $this->status
        
        return $json_response;
    }

    public function validate_data($data)
    {
        if ( !isset( $data['callback'] ) ) {
            //return false;
        }
        
        if ( !isset( $data['printer_ip'] ) ) {
            return false;
        }
        
        if ( !isset( $data['header'] ) ) {
            return false;
        }
        
        if ( !isset( $data['lines'] ) ) {
            return false;
        }

        return true;
    }
    
    public function get_json_response($message)
    {
        if( $message != 'ok' )
        {
            $this->status = 'Pendiente';
        }

        $json_response = json_encode(
                                [ 
                                    'status' => $this->status,
                                    'message' => $message
                                ]
                            );
        
        if( !$json_response )
        {
            $json_response = json_encode(
                [ 
                    'status' => $this->status
                ]
            );
        }

        return $json_response;
    }

    public function sent_to_print($data)
    {        
        $printer_IP = $data['printer_ip'];

        $header = (object)$data['header'];
        $lines = (object)$data['lines'];

        try {
            $connector = new NetworkPrintConnector($printer_IP, 9100);
            $printer = new Printer($connector);
            
            // Initialize
            $printer->initialize();
            
            // The B is for the bell chime, the second digit for the number of requested beeps, and the third for the time between beeps.
            $printer -> getPrintConnector() -> write(PRINTER::ESC . "B" . chr(3) . chr(2));

            // Build format
            if ( isset($header->empresa) ) {
                $printer = $this->build_format_invoice( $printer, $header, $lines );
            }else{
                $printer = $this->build_format_order( $printer, $header, $lines );
            }

            $printer->selectPrintMode(); // Reset

            $printer->feed(3);
            $printer->cut();

            // Always close the printer! On some PrintConnectors, no actual data is sent until the printer is closed.
            $printer->close();

        } catch (Exception $e) {

            // Log the message locally OR use a tool like Bugsnag/Flare to log the error
            //Log::debug($e->getMessage());
         
            return $e->getMessage();
        }

        return 'ok';
    }

    public function build_format_invoice( $printer, $header, $lines )
    {
        //dd( $header );
        // Logo
        /**/
        $logo = file_get_contents( $header->empresa['url_logo'] );
        Storage::put('logo.png', $logo);
        $logo = EscposImage::load( storage_path() . '/app/logo.png', false);
        $printer -> bitImage($logo, Printer::IMG_DEFAULT);

        // Datos empresa
        $printer->selectPrintMode(49);
        $printer->setJustification( Printer::JUSTIFY_CENTER );
        $printer->text( $header->empresa['descripcion'] . "\n");
        $printer->selectPrintMode(41);
        $printer->text( $header->empresa['descripcion_tipo_documento_identidad'] . ':' . $header->empresa['numero_identificacion'] . '-' . $header->empresa['digito_verificacion'] . "\n");
        
        $barrio = '';
        if ( $header->empresa['barrio'] != '') {
            $barrio = ', BRR ' .  $header->empresa['barrio'];
        }
        $printer->text( $header->empresa['direccion1'] . $barrio . "\n");
        $printer->text( $header->empresa['descripcion_ciudad'] . "\n");
        
        $telefono2 = '';
        if ( $header->empresa['telefono2'] != '') {
            $telefono2 = ', ' .  $header->empresa['telefono2'];
        }
        $printer->text( 'Teléfono(s): ' . $header->empresa['telefono1'] . $telefono2 . "\n");

        if ( $header->empresa['email'] != '') {
            $printer->text( 'Email: ' . $header->empresa['email'] . "\n");
        }

        if ( $header->empresa['pagina_web'] != '') {
            $printer->text( $header->empresa['pagina_web'] . "\n");
        }

        // ____________________________________________________________

        $lineas_encabezado = explode('<br>',$header->etiquetas['encabezado']);
        foreach ($lineas_encabezado as $key => $message) {
            $printer->text( $message . "\n");
        }       

        // Datos de la factura y el cliente       

        //$printer->selectPrintMode(32);
        $printer->text( "\n");
        $printer->text( $header->transaction_label . ' ' . $header->number_label . "\n");
        //$printer->selectPrintMode(56);
        //$printer->text( $header->number_label . "\n");
        //$printer->text( "Impresion de prueba\n");
        $printer->setJustification(); // Reset
        
        $printer->selectPrintMode(41);
        $printer->text( "Fecha: " . $header->date . "\n");
        $printer->text( "Cliente: " . $header->customer_name . "\n");
        $printer->text( $header->cliente_info['descripcion_tipo_documento_identidad'] . ':' . $header->cliente_info['numero_identificacion'] . '-' . $header->cliente_info['digito_verificacion'] . "\n");

        $barrio = '';
        if ( $header->cliente_info['barrio'] != '') {
            $barrio = ', BRR ' .  $header->cliente_info['barrio'];
        }
        $printer->text( "Dirección: " . $header->cliente_info['direccion1'] . $barrio .  "\n");
        
        $telefono2 = '';
        if ( $header->cliente_info['telefono2'] != '') {
            $telefono2 = ', ' .  $header->cliente_info['telefono2'];
        }
        $printer->text( "Teléfono: " . $header->cliente_info['telefono1'] . $telefono2 . "\n");
        $printer->text( "Atendido por: " . $header->seller_label . "\n");
        $printer->text( "Detalle: " . $header->detail . "\n\n");

        // Lineas de registros
        $printer->selectPrintMode(41);
        $printer->text( "______________________________\n");
        $printer->text( "   ITEM    Cant/Precio   Total\n");

        $total_factura = 0;
        foreach ($lines as $line) {

            $item_name = $line['item'];

            $end = 10;
            
            $texto_inicial = '*' . substr( $item_name, 0, $end);
            if ( strlen($texto_inicial) < $end ) {
                $caracteres_restantes = $end - strlen($texto_inicial);
                for ($i=0; $i < $caracteres_restantes; $i++) { 
                    $texto_inicial .= ' ';
                }
            }

            $texto_medio = $line['quantity'] . "/$" . number_format($line['unit_price'],'0',',','.');
            if ( strlen($texto_medio) < 10 ) {
                $caracteres_restantes = 10 - strlen($texto_medio);
                for ($i=0; $i < $caracteres_restantes; $i++) { 
                    $texto_medio = ' ' . $texto_medio;
                }
            }

            $texto_final = "$" . number_format($line['total_amount'],'0',',','.');
            if ( strlen($texto_final) < 8 ) {
                $caracteres_restantes = 8 - strlen($texto_final);
                for ($i=0; $i < $caracteres_restantes; $i++) { 
                    $texto_final = ' ' . $texto_final;
                }
            }


            // Draw first line
            $printer->text(  $texto_inicial . " " . $texto_medio . " " . $texto_final . "\n" );

            $length_pendiente = strlen($item_name) - $end;
            $start = $end;
            $end = 25;
            //while ($length_pendiente > 3) {
              //  $end += 1;   

                $printer->text( " " . substr( $item_name, $start, $end) . "\n" );

                //$length_pendiente = $length_pendiente - $start;

                //$start = $end;
            //}

            $total_factura += $line['total_amount'];
        }

        $printer->text( "______________________________\n");
        $printer->text( "     Total Factura:    $" . number_format($total_factura,'0',',','.') . "\n" );

        
        $printer->text( "\n\n Firma del Aceptante:  \n\n\n" );

        $lineas_pie_pagina = explode('<br>',$header->etiquetas['pie_pagina']);
        
        $printer->setJustification( Printer::JUSTIFY_CENTER );
        $printer->selectPrintMode(41);
        foreach ($lineas_pie_pagina as $key => $message) {
            $printer->text( $message . "\n");
        }

        return $printer;
    }

    public function build_format_order( $printer, $header, $lines )
    {
        $printer->selectPrintMode(32);
        $printer->setJustification( Printer::JUSTIFY_CENTER );
        $printer->text( $header->transaction_label . "\n");
        $printer->selectPrintMode(56);
        $printer->text( $header->number_label . "\n");
        //$printer->text( "Impresion de prueba\n");
        $printer->setJustification(); // Reset
        
        $printer->selectPrintMode(41);
        $printer->text( "Fecha: " . $header->date . "\n");
        $printer->text( "Cliente: " . $header->customer_name . "\n");
        $printer->text( "Atiende: " . $header->seller_label . "\n");
        
        $printer->selectPrintMode(49);
        $printer->text( "Detalle: " . $header->detail . "\n\n");

        $printer->selectPrintMode(41);
        $printer->text( "___________________________\n");
        $printer->text( " CANT.        ITEM \n");

        $printer->selectPrintMode(49);

        $total_factura = 0;
        foreach ($lines as $line) {

            $item_name = $line['item'];

            $end = 20;
            
            $printer->text( " " . $line['quantity'] . "-" . substr( $item_name, 0, $end) . "\n" );

            $length_pendiente = strlen($item_name) - $end;
            $start = $end;
            $end = 25;
                
            while ($length_pendiente > 3) {
                $end += 1;   

                $printer->text( "    " . substr( $item_name, $start, $end) . "\n" );

                $length_pendiente = $length_pendiente - $start;

                $start = $end;
            }

           $total_factura += $line['total_amount'];
        }

        $printer->text( "\n     Total Pedido:    $" . number_format($total_factura,'0',',','.') . "\n" );

        return $printer;
    }

    public function example_page()
    {
        
    }

}
