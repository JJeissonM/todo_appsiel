<?php

namespace App\Http\Controllers\FacturacionElectronica;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AplicacionController extends Controller
{
    public function index()
    {
    	return view('facturacion_electronica.index');
    }

    public function consultar_documentos_emitidos()
    {
    	return view('facturacion_electronica.consultar_documentos_emitidos');
    }

}
