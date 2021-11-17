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


// Modelos
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;

use App\Nomina\Services\ContabilizacionDocumentoNomina;

class ContabilizacionDocumentoController extends TransaccionController
{

    public function contabilizar( Request $request )
    {
        $servicio_contabilizacion = new ContabilizacionDocumentoNomina( (int)$request->nom_doc_encabezado_id );

        if ( $servicio_contabilizacion->get_estado() == 'contabilizado' )
        {
            return View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento_contabilizado', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'accion' => 'validar' ] )->render();
        }

        $lineas_html_movimiento_contable = $servicio_contabilizacion->get_lineas_html_movimiento_contable();

        if ( $request->almacenar_registros )
        {
            // COntabilizar y generar movimientos de CxC y CxP
            $servicio_contabilizacion->almacenar_movimiento_contable();
            //$servicio_contabilizacion->encabezado_doc->estado = 'Cerrado';
            //$servicio_contabilizacion->encabezado_doc->save();
        }
        
        $vista = View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'lineas_tabla' => $lineas_html_movimiento_contable, 'valor_debito_total' => $servicio_contabilizacion->valor_debito_total, 'valor_credito_total' => $servicio_contabilizacion->valor_credito_total, 'contabilizado' => $request->almacenar_registros ] )->render();
        
        return $vista;
    }

    public function retirar( $doc_encabezado_id )
    {
        $servicio_contabilizacion = new ContabilizacionDocumentoNomina( (int)$doc_encabezado_id );

        $resultado_retiro = $servicio_contabilizacion->retirar_contabilizacion();

        $mensaje = 'El documento de nómina fue retirado exitosamente de la contabilidad.';
        $clase = 'success';

        if( $resultado_retiro <> 'ok' )
        {
            $mensaje = $resultado_retiro;
            $clase = 'warning';
        }

        return View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento_contabilizado', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'accion' => 'retirar', 'mensaje' => $mensaje, 'clase' => $clase ] )->render();   
    }    
}