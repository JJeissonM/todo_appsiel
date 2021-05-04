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
use App\Nomina\EmpleadoOrdenDeTrabajo;
use App\Nomina\ItemOrdenDeTrabajo;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\ModosLiquidacion\ModoLiquidacion; // Facade

class OrdenDeTrabajoController extends TransaccionController
{

	public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id);

        $orden_de_trabajo = OrdenDeTrabajo::find( $id );
        $doc_encabezado = OrdenDeTrabajo::find( $id );

        $documento_vista = $this->vista_preliminar( $orden_de_trabajo, 'show' );

        $id_transaccion = $this->transaccion->id;
        $empresa = $this->empresa;

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $orden_de_trabajo->tipo_documento_app->prefijo . ' ' . $orden_de_trabajo->consecutivo );

        return view( 'nomina.ordenes_de_trabajo.show', compact('id', 'botones_anterior_siguiente', 'orden_de_trabajo', 'documento_vista', 'id_transaccion', 'empresa', 'miga_pan', 'doc_encabezado') );
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
        $concepto = NomConcepto::find( (int)Input::get('nom_concepto_id') );
        $documento = NomDocEncabezado::find( (int)Input::get('nom_doc_encabezado_id') );

        // Se obtienen los Empleados del documento
        $empleados = $documento->empleados;

        return View::make( 'nomina.create_registros2_tabla', [ 'empleados' => $empleados, 'concepto' => $concepto ] )->render();
    }


    
    public function cambiar_cantidad_horas_empleados( $orden_trabajo_id, $nom_concepto_id, $nom_contrato_id, $nueva_cantidad_horas )
    {
        $linea_empleado = EmpleadoOrdenDeTrabajo::where( [ 
                                                            'orden_trabajo_id' => $orden_trabajo_id,
                                                            'nom_concepto_id' => $nom_concepto_id,
                                                            'nom_contrato_id' => $nom_contrato_id
                                                        ])
                                                ->get()
                                                ->first();
        if ( is_null($linea_empleado) )
        {
            return 'false';
        }

        $nuevo_valor_devengo = (float)$nueva_cantidad_horas * $linea_empleado->valor_por_hora;

        $linea_empleado->cantidad_horas = (float)$nueva_cantidad_horas;
        $linea_empleado->valor_devengo = $nuevo_valor_devengo;
        $linea_empleado->modificado_por = Auth::user()->email;
        $linea_empleado->save();

        $this->modificar_linea_registro_documento_nomina( $linea_empleado );

        return 'true';        
    }
    
    public function cambiar_valor_por_hora_empleados( $orden_trabajo_id, $nom_concepto_id, $nom_contrato_id, $nuevo_valor_por_hora )
    {
        $linea_empleado = EmpleadoOrdenDeTrabajo::where( [ 
                                                            'orden_trabajo_id' => $orden_trabajo_id,
                                                            'nom_concepto_id' => $nom_concepto_id,
                                                            'nom_contrato_id' => $nom_contrato_id
                                                        ])
                                                ->get()
                                                ->first();
        if ( is_null($linea_empleado) )
        {
            return 'false';
        }

        $nuevo_valor_devengo = (float)$nuevo_valor_por_hora * $linea_empleado->cantidad_horas;

        $linea_empleado->valor_por_hora = (float)$nuevo_valor_por_hora;
        $linea_empleado->valor_devengo = $nuevo_valor_devengo;
        $linea_empleado->modificado_por = Auth::user()->email;
        $linea_empleado->save();

        $this->modificar_linea_registro_documento_nomina( $linea_empleado );

        return 'true';        
    }

    public function modificar_linea_registro_documento_nomina( $linea_empleado_orden_trabajo )
    {
        $linea_documento_nomina = NomDocRegistro::where( [ 
                                                            'nom_doc_encabezado_id' => $linea_empleado_orden_trabajo->orden_trabajo->nom_doc_encabezado_id,
                                                            'orden_trabajo_id' => $linea_empleado_orden_trabajo->orden_trabajo->id,
                                                            'nom_concepto_id' => $linea_empleado_orden_trabajo->nom_concepto_id,
                                                            'nom_contrato_id' => $linea_empleado_orden_trabajo->nom_contrato_id
                                                                ])
                                                        ->get()
                                                        ->first();

        // Actualizar registro en documento de nomina
        $linea_documento_nomina->cantidad_horas = $linea_empleado_orden_trabajo->cantidad_horas;
        $linea_documento_nomina->valor_devengo = $linea_empleado_orden_trabajo->valor_devengo;
        $linea_documento_nomina->modificado_por = Auth::user()->email;
        $linea_documento_nomina->save();

        $linea_documento_nomina->encabezado_documento->actualizar_totales();
    }
    
    public function cambiar_cantidad_items( $orden_trabajo_id, $inv_producto_id, $nueva_cantidad )
    {
        $linea_registro = ItemOrdenDeTrabajo::where( [ 
                                                            'orden_trabajo_id' => $orden_trabajo_id,
                                                            'inv_producto_id' => $inv_producto_id
                                                        ])
                                                ->get()
                                                ->first();
        if ( is_null($linea_registro) )
        {
            return 'false';
        }

        $nuevo_costo_total = (float)$nueva_cantidad * $linea_registro->costo_unitario;

        $linea_registro->cantidad = (float)$nueva_cantidad;
        $linea_registro->costo_total = $nuevo_costo_total;
        $linea_registro->modificado_por = Auth::user()->email;
        $linea_registro->save();

        return 'true';        
    }
}