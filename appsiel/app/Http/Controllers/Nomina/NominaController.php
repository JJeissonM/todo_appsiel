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
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;
use App\Nomina\AgrupacionConcepto;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\ModosLiquidacion\ModoLiquidacion; // Facade

class NominaController extends TransaccionController
{
    protected $total_devengos_empleado = 0;
    protected $total_deducciones_empleado = 0;
    protected $vec_totales = [];
    protected $pos = 0;
    protected $registros_procesados = 0;
    protected $vec_campos;
    protected $array_ids_modos_liquidacion_automaticos = [ 7, 1, 6, 3, 4, 8]; // 7: Tiempo NO Laborado, 1: tiempo laborado, 6: aux. transporte, 3: cuotas, 4: prestamos, 8: seguridad social

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $miga_pan = [
                ['url'=>'NO','etiqueta'=>'Nómina']
            ];

        return view( 'nomina.index', compact( 'miga_pan' ) );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'layouts.create', '' );
    }

    /*
        Por cada empleado activo liquida los conceptos automáticos, las cuotas y préstamos
        Además actualiza el total de devengos y deducciones en el documento de nómina
    */
    public function liquidacion($id)
    {
        $this->registros_procesados = 0;

        $usuario = Auth::user();

        $core_empresa_id = $usuario->empresa_id;

        $documento = NomDocEncabezado::find($id);

        // Se obtienen los Empleados del documento
        $empleados_documento = $documento->empleados;

        // Guardar los valores para cada empleado      
        foreach ($empleados_documento as $empleado) 
        {
            $cant = count( $this->array_ids_modos_liquidacion_automaticos );

            for ($i=0; $i < $cant; $i++) 
            { 
                $this->liquidar_automaticos_empleado( $this->array_ids_modos_liquidacion_automaticos[$i], $empleado, $documento, $usuario);
            }
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'flash_message','Liquidación realizada correctamente. Se procesaron '.$this->registros_procesados.' registros.' );
    }

    /*
        Recibe doc. de nómina, al empleado y el modo de liquidación para calcular el valor de devengo o deducción de cada concepto
    */
    public function liquidar_automaticos_empleado($modo_liquidacion_id, $empleado, $documento_nomina, $usuario)
    {
        $conceptos_automaticos = NomConcepto::where('estado','Activo')->where('modo_liquidacion_id', $modo_liquidacion_id)->get();

        foreach ( $conceptos_automaticos as $concepto )
        {
            $cant = 0;
            if ( $modo_liquidacion_id != 7 ) // Si no es TNL, de esta pueden haber varias en el mismo Doc.
            {
                // Se valida si ya hay una liquidación previa del concepto en ese documento
                $cant = NomDocRegistro::where( 'nom_doc_encabezado_id', $documento_nomina->id)
                                        ->where('core_tercero_id', $empleado->core_tercero_id)
                                        ->where('nom_concepto_id', $concepto->id)
                                        ->count();
            }
                

            if ( $cant != 0 ) 
            {
                continue;
            }

            // Se llama al subsistema de liquidación
            $liquidacion = new LiquidacionConcepto( $concepto->id, $empleado, $documento_nomina);

            $valores = $liquidacion->calcular( $concepto->modo_liquidacion_id );

            foreach( $valores as $registro )
            {
                if( ($registro['valor_devengo'] + $registro['valor_deduccion']) > 0 )
                {
                    $this->almacenar_linea_registro_documento( $documento_nomina, $empleado, $concepto, $registro, $usuario);

                    $this->registros_procesados++;
                }
            }            
        } // Fin Por cada concepto
    }

    public function almacenar_linea_registro_documento($documento_nomina, $empleado, $concepto, $registro, $usuario)
    {
        NomDocRegistro::create(
                                    ['nom_doc_encabezado_id' => $documento_nomina->id ] + 
                                    ['fecha' => $documento_nomina->fecha] + 
                                    ['core_empresa_id' => $documento_nomina->core_empresa_id] +  
                                    ['nom_concepto_id' => $concepto->id ] + 
                                    ['core_tercero_id' => $empleado->core_tercero_id ] + 
                                    ['nom_contrato_id' => $empleado->id ] + 
                                    ['estado' => 'Activo'] + 
                                    ['creado_por' => $usuario->email] + 
                                    ['modificado_por' => '']+ 
                                    $registro
                                );
    }

    /**
     * Muestra un documento de liquidación con sus registros
     */
    public function show($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        $reg_anterior = NomDocEncabezado::where('id', '<', $id)->max('id');
        $reg_siguiente = NomDocEncabezado::where('id', '>', $id)->min('id');

        $view_pdf = $this->vista_preliminar($id,'show');

        $miga_pan = [
                  ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                  ['url'=>'NO','etiqueta' => 'Consulta' ]
              ];

        return view( 'nomina.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') ); 

    }


    public function nomina_print($id)
    {
      $view_pdf = $this->vista_preliminar($id,'imprimir');

      $tam_hoja = 'folio';
      $orientacion='landscape';
      $pdf = \App::make('dompdf.wrapper');
      $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);
      return $pdf->stream('nomina'.$this->encabezado_doc->documento_app.'.pdf');
    }


    // Generar vista para SHOW o IMPRIMIR
    public function vista_preliminar($id,$vista)
    {

        $this->encabezado_doc =  NomDocEncabezado::get_un_registro($id);

        $empleados =$this->encabezado_doc->empleados;

        $conceptos = NomConcepto::conceptos_del_documento($this->encabezado_doc->id);

        $tabla = '<style> .celda_firma { width: 100px; }  .celda_nombre_empleado { width: 150px; } .table.sticky th {position: sticky; top: 0;} </style>
                    <br>
                    <div class="table-responsive">
                     <table  class="tabla_registros table table-striped sticky" style="margin-top: 1px; width: 100%;">
                    <thead>
                      <tr class="encabezado">
                          <th>
                             No.
                          </th>
                          <th>
                             Empleado
                          </th>
                          <th>
                             Identifcación
                          </th>';
        foreach ($conceptos as $registro)
        {          
          $tabla.='<th>'.$registro->abreviatura.'</th>';
        }

        $tabla.='<th>Tot. <br> Devengos</th>
                    <th>Tot. <br> Deducciones</th>
                    <th>Total a pagar</th>
                    <th width="100px">Firma</th>
                    </tr>
                    </thead>
                    <tbody>';

        $total_1=0;
        $i=1;

        $this->vec_totales = array_fill(0, count($conceptos)+3, 0);  
        
        foreach ($empleados as $empleado)
        {          
            $this->total_devengos_empleado = 0;
            $this->total_deducciones_empleado = 0;

            $tabla.='<tr>
                    <td>'.$i.'</td>
                    <td class="celda_nombre_empleado">'.$empleado->tercero->descripcion.'</td>
                    <td>'.number_format($empleado->tercero->numero_identificacion, 0, ',', '.').'</td>';

            $this->pos = 0;
            foreach ($conceptos as $un_concepto)
            {          
                $valor = $this->get_valor_celda( NomDocRegistro::where('nom_doc_encabezado_id',$this->encabezado_doc->id)->where('core_tercero_id',$empleado->core_tercero_id)->where('nom_concepto_id',$un_concepto->nom_concepto_id)->get(), $un_concepto );
                
                $tabla.='<td>'.$valor.'</td>';
                $this->pos++;
            }

            $total_devengos_empleado = NomDocRegistro::where( 'nom_doc_encabezado_id', $this->encabezado_doc->id )
                                                        ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                                        ->sum('valor_devengo');

            $total_deducciones_empleado = NomDocRegistro::where( 'nom_doc_encabezado_id', $this->encabezado_doc->id )
                                                        ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                                        ->sum('valor_deduccion');

            $tabla.='<td>'.Form::TextoMoneda( $total_devengos_empleado ).'</td>';

            $tabla.='<td>'.Form::TextoMoneda( $total_deducciones_empleado ).'</td>';

            $tabla.='<td>'.Form::TextoMoneda( $total_devengos_empleado - $total_deducciones_empleado ).'</td>';

            $tabla.='<td class="celda_firma"> &nbsp; </td>';

            $this->vec_totales[$this->pos] += $total_devengos_empleado;
            $this->pos++;
            $this->vec_totales[$this->pos] += $total_deducciones_empleado;
            $this->pos++;
            $this->vec_totales[$this->pos] += $total_devengos_empleado - $total_deducciones_empleado;

            $tabla.='</tr>';
            $i++;
        }

        $tabla.='<tr><td></td><td></td><td></td>';

        $cant = count( $this->vec_totales );
        for ($j=0; $j < $cant; $j++)
        {
            $tabla.='<td>'.Form::TextoMoneda( $this->vec_totales[$j] ).'</td>';
        }
        $tabla.='<td> &nbsp; </td>';
        $tabla.='</tr></tbody></table></div>';

        // DATOS ADICIONALES
        $tipo_doc_app = TipoDocApp::find($this->encabezado_doc->core_tipo_doc_app_id);
        $descripcion_transaccion = $tipo_doc_app->descripcion;

        $elaboro = $this->encabezado_doc->creado_por;
        $empresa = Empresa::find($this->encabezado_doc->core_empresa_id);
        $ciudad = DB::table('core_ciudades')
              ->where('id','=',$empresa->codigo_ciudad)
              ->value('descripcion');

        $encabezado_doc = $this->encabezado_doc;

        $firmas = '';
              
        $view_1 = View::make('nomina.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista','ciudad') )->render();

        $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla.$firmas.'<div class="page-break"></div>';
        
        return $view_pdf;
    }

    function get_valor_celda($registro, $un_concepto)
    {
        if ( count($registro) > 0) 
        {
            // Se suma devengo y deduccion (alguno de los dos es cero)
            $valor = Form::TextoMoneda( $registro[0]->valor_devengo + $registro[0]->valor_deduccion );

            switch ($un_concepto->naturaleza) 
            {
                case 'devengo':
                    $this->total_devengos_empleado += $registro[0]->valor_devengo;
                    break;
                case 'deduccion':
                    $this->total_deducciones_empleado += $registro[0]->valor_deduccion;
                    break;
                
                default:
                    # code...
                    break;
            }

            $this->vec_totales[$this->pos] += $registro[0]->valor_devengo + $registro[0]->valor_deduccion;
        }else{
            $valor = '';
        }

        return $valor;
    }

    // Retiro de conceptos con modo liquidacion automatica
    public function retirar_liquidacion($id)
    {
        $documento_nomina = NomDocEncabezado::find( $id );
        $registros_documento = $documento_nomina->registros_liquidacion;

        foreach ( $registros_documento as $registro )
        {
            if ( !is_null( $registro->concepto ) && !is_null($registro->contrato) )
            {
                if ( in_array( $registro->concepto->modo_liquidacion_id, $this->array_ids_modos_liquidacion_automaticos) )
                {
                    // Se llama al subsistema de liquidación
                    $liquidacion = new LiquidacionConcepto( $registro->concepto->id, $registro->contrato, $documento_nomina);
                    $liquidacion->retirar( $registro->concepto->modo_liquidacion_id, $registro );
                }
            }   
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'mensaje_error','Registros automáticos retirados correctamente.' );
    }

    function actualizar_totales_documento($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);
        $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_devengo');
        $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_deduccion');
        $documento->save();
    }
    
}