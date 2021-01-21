<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use View;
use Lava;
use Input;
use NumerosEnLetras;
use Form;


use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

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

class ReciboCajaController extends TransaccionController
{

    public function show($id)
    {
        $this->set_variables_globales();
        $id_transaccion = $this->transaccion->id;

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        $encabezado_documento = TesoDocEncabezado::find( $id );

        $empresa = $this->empresa;

        $documento_vista = '';
        $vista_impresion = false;

        $registro_referencia_tercero = $this->get_registro_referencia_tercero( $doc_encabezado );

        $documento_vista2 = View::make('tesoreria.recaudos.recibos_caja_vista',compact( 'encabezado_documento', 'empresa', 'vista_impresion', 'registro_referencia_tercero' ) )->render();
        $id_transaccion = $doc_encabezado->core_tipo_transaccion_id;

        $miga_pan = [
                [ 'url' => $this->app->app.'?id='.Input::get('id'),'etiqueta' => $this->app->descripcion ],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $this->modelo->descripcion ],
                ['url'=>'NO','etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
            ];
        
        return view( 'tesoreria.recaudos.recibos_caja', compact( 'id', 'botones_anterior_siguiente', 'documento_vista2', 'documento_vista', 'id_transaccion', 'miga_pan','doc_encabezado','empresa', 'encabezado_documento') );
    }

    public function get_registro_referencia_tercero( $doc_encabezado )
    {
        $movimiento = TesoMovimiento::where( [
                                                [ 'core_tipo_transaccion_id', '=', $doc_encabezado->core_tipo_transaccion_id ],
                                                [ 'core_tipo_doc_app_id', '=', $doc_encabezado->core_tipo_doc_app_id ],
                                                [ 'consecutivo', '=', $doc_encabezado->consecutivo ]
                                            ])
                                        ->get()
                                        ->first();

        if ( is_null($movimiento) )
        {
            return null;
        }

        return $movimiento->get_registro_referencia_tercero();
    }

    public function imprimir($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        $encabezado_documento = TesoDocEncabezado::find( $id );

        $empresa = Empresa::find( $encabezado_documento->core_empresa_id );
        
        $vista_impresion = true;

        $registro_referencia_tercero = $this->get_registro_referencia_tercero( $encabezado_documento );

        $view = View::make('tesoreria.recaudos.recibos_caja_vista',compact( 'encabezado_documento', 'empresa', 'vista_impresion', 'registro_referencia_tercero' ) )->render();

        $documento_vista = View::make( 'layouts.pdf3', compact( 'view' ) )->render();
       
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista )->setPaper($tam_hoja,$orientacion);

        return $pdf->stream( $encabezado_documento->tipo_documento_app->descripcion.' - '.$encabezado_documento->tipo_documento_app->prefijo.' '.$encabezado_documento->consecutivo.'.pdf');
    }


    /**
        Anular
     */
    public function anular($id)
    {
        $documento = TesoDocEncabezado::find($id);
        $modificado_por = Auth::user()->email;

        $array_wheres = [ 'core_empresa_id'=>$documento->core_empresa_id, 
                            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
                            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
                            'consecutivo' => $documento->consecutivo ];

        TesoMovimiento::where($array_wheres)->delete();

        $documento->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        return redirect( 'teso_recibo_caja_show/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Documento de anulado correctamente.');
    }
}