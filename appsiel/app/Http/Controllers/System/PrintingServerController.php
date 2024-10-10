<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Input;

class PrintingServerController extends Controller
{
    public function send_printing_to_server()
    {
        $url = Input::get('url_servidor_impresion');
        try {
            $client = new Client();

            $data = Input::get();

            $response = $client->request( 'GET', $url, ['query' => $data] );

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
        }

        $message = 'Servidor de impresion no encontrado: ' . $url;
        if ( $response != null) {
            $message = json_decode( (string) $response->getBody(), true );
        }

        return $message;
    }
}