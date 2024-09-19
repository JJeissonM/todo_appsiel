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

            //dd( $e->getMessage());
            $response = $e->getResponse();
        }

        return json_decode( (string) $response->getBody(), true );
    }
}