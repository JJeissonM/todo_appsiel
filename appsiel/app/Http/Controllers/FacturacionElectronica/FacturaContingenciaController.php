<?php

namespace App\Http\Controllers\FacturacionElectronica;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FacturaElectronicaContingenciaVentasController extends Controller
{
    public function index()
    {
    	return view('facturacion_electronica.index');
    }
}
