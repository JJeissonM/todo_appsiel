<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Sistema\Services\PrintingServer\ExampleRawbt;
use App\Sistema\Services\PrintingServer\PrintingJobService;
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

    public function test_printing_form()
    {
        return view('system.test_printing_form');
    }

    public function test_print_example_rawbt()
    {
        return (new ExampleRawbt())->print2();
    }

    public function feed_paper( $line_numbers = 1 )
    {
        return (new ExampleRawbt())->feed_paper( $line_numbers );
    }

    public function feed_reverse_paper( $line_numbers = 1 )
    {
        return (new ExampleRawbt())->feed_reverse_paper( $line_numbers );
    }

    public function cut_paper()
    {
        return (new ExampleRawbt())->cut_paper();
    }
}