<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;

// Objetos
use App\Sistema\Html\BotonesAnteriorSiguiente;


// Modelos
use App\Sistema\Modelo;
use App\Core\Tercero;
use App\Core\TipoDocApp;

use App\Matriculas\Grado;
use App\Matriculas\Estudiante;
use App\Core\Colegio;
use App\Core\Empresa;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\CxC\CxcMovimiento;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabCuenta;

class ProcessController extends Controller
{
    public function re_accounting_one_document( $doc_header_id )
    {
        $document_header = TesoDocEncabezado::find( $doc_header_id );
        $document_header->accounting_movement();

        return $document_header;
    }
}