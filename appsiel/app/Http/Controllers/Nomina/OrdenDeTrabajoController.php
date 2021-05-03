<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;
use NumerosEnLetras;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Nomina\RegistrosDocumentosController;

use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\ProgramacionVacacion;
use App\Nomina\OrdenDeTrabajo;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\ModosLiquidacion\ModoLiquidacion; // Facade

class OrdenDeTrabajoController extends TransaccionController
{

	public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id);

        $orden_de_trabajo = OrdenDeTrabajo::find( $id );

        $documento_vista = $this->vista_preliminar( $orden_de_trabajo, 'show' );

        $id_transaccion = $this->transaccion->id;
        $empresa = $this->empresa;

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $orden_de_trabajo->tipo_documento_app->prefijo . ' ' . $orden_de_trabajo->consecutivo );

        return view( 'nomina.ordenes_de_trabajo.show', compact('id', 'botones_anterior_siguiente', 'orden_de_trabajo', 'documento_vista', 'id_transaccion', 'empresa', 'miga_pan') );
    }

    public function vista_preliminar( $orden_de_trabajo )
    {
    	$documento_vista = View::make( 'nomina.ordenes_de_trabajo.documento_vista', compact('orden_de_trabajo') )->render();
    	return $documento_vista;
    }

    public function imprimir()
    {
    	echo "imprimir";
    }

    public function get_tabla_empleados_ingreso_registros()
    {
        $array = RegistrosDocumentosController::get_array_tabla_registros( (int)Input::get('nom_concepto_id'), (int)Input::get('nom_doc_encabezado_id'), '' );
        //dd($array);
        return View::make( 'nomina.create_registros2_tabla', $array )->render();
    }
}