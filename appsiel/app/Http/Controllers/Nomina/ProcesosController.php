<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use View;
use Auth;

use App\Nomina\TransaccionesViaInterfaz\ArchivoPlano;

use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomConcepto;
use App\Nomina\NomContrato;

class ProcesosController extends Controller
{
    //

    public function procesar_archivo_plano( Request $request )
    {
    	$nom_doc_encabezado_id = $request->nom_doc_encabezado_id;
    	$encabezado_documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

    	$archivo = new ArchivoPlano( $encabezado_documento, file( $request->archivo_plano ) );

    	$lineas_archivo_plano = $archivo->validar_estructura_archivo();

    	return View::make( 'nomina.procesos.transacciones_via_interface.lineas_registros_guardar_archivo_plano', compact( 'lineas_archivo_plano', 'nom_doc_encabezado_id' ) )->render();
    }

    public function almacenar_registros_via_interface( Request $request )
    {
    	$nom_doc_encabezado_id = $request->documento_encabezado_id;
    	$encabezado_documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

    	$lineas_registros = json_decode( $request->lineas_registros );
    	$cantidad_registros = 0;
    	foreach ($lineas_registros as $linea )
    	{ 
    		if ( !$linea->con_errores)
    		{
    			$registro = new NomDocRegistro;

    			$concepto = NomConcepto::find( (int)$linea->nom_concepto_id );
    			$contrato = NomContrato::find( (int)$linea->nom_contrato_id );
    			if ( is_null($contrato) ) {
    				dd($linea);
    			}
    			
    			// Cuando se indica Cantidad de horas, se descarta el valor ingresado
    			$valor_a_liquidar = (float)$linea->valor;
    			if ( (float)$linea->cantidad_horas != 0 )
    			{
    				$valor_a_liquidar = $concepto->get_valor_hora_porcentaje_sobre_basico( $contrato->salario_x_hora(), (float)$linea->cantidad_horas );
    			}

    			$valores = get_valores_devengo_deduccion( $concepto->naturaleza, $valor_a_liquidar );

    			$registro->fill( 
    								[ 
    									'nom_doc_encabezado_id' => $encabezado_documento->id,
    									'core_tercero_id' => (int)$linea->core_tercero_id,
    									'nom_contrato_id' => (int)$linea->nom_contrato_id,
    									'fecha' => $encabezado_documento->fecha,
    									'core_empresa_id' => $encabezado_documento->core_empresa_id,
    									'nom_concepto_id' => (int)$linea->nom_concepto_id,
    									'cantidad_horas' => (float)$linea->cantidad_horas,
    									'valor_devengo' => $valores->devengo,
    									'valor_deduccion'  => $valores->deduccion,
    									'estado' => 'Activo',
    									'creado_por' => Auth::user()->email
    								]
    							);

    			$registro->save();

    			$cantidad_registros++;
    		}
    	}

    	return redirect( 'index_procesos/nomina.procesos.transacciones_via_interface?id=17' )->with('flash_message','Se almacenaron <b>' . $cantidad_registros . ' registros </b> correctamente en el documento <b>' . $encabezado_documento->descripcion . '</b>.' );
    }
}
