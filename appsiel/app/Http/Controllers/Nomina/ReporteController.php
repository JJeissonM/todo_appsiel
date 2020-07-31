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

use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;


// Modelos

use App\Sistema\Aplicacion;
use App\Core\Empresa;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;

class ReporteController extends Controller
{
   public function reportes()
    {
        $app = Aplicacion::find(Input::get('id'));

        $opciones1 = NomDocEncabezado::all();
        $vec1['']='';
        foreach ($opciones1 as $opcion){
            $vec1[$opcion->id] = $opcion->descripcion;
        }
        $documentos = $vec1;

        $personas = NomContrato::get_empleados( '' );
        $vec2['Todos']='Todos';
        foreach ($personas as $opcion){
            $vec2[$opcion->core_tercero_id] = $opcion->empleado;
        }
        $empleados = $vec2;

        $miga_pan = [
                ['url' => $app->app.'?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                ['url'=>$app->app.'?id='.Input::get('id'),'etiqueta'=>'Informes y listados']
            ];

        return view('nomina.reportes.desprendibles_de_pago', compact('miga_pan', 'documentos', 'empleados') );
    }

    /**
     * ajax_reporte_desprendibles_de_pago
     *
     */
    public function ajax_reporte_desprendibles_de_pago(Request $request)
    {
        return $this->generar_reporte_desprendibles_de_pago($request->nom_doc_encabezado_id,  $request->core_tercero_id);
    }

    /**
     * ajax_reporte_desprendibles_de_pago
     *
     */
    public function nomina_pdf_reporte_desprendibles_de_pago()
    {
        $tabla = $this->generar_reporte_desprendibles_de_pago(Input::get('nom_doc_encabezado_id'), Input::get('core_tercero_id') );

        $vista = '<html>
                    <head>
                        <title>Reporte desprendible de pago</title>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                        <style>
                            @page { 
                                margin: 0.7cm;
                            }
                            .page-break {
                                page-break-after: always;
                            }
                            .cuadro {
                                border: 1px solid;
                                border-radius: 10px;
                                padding: 5px;
                            }
                            .table td {
                                padding: 0px;
                            }
                        </style>    
                    </head>
                    <body>
                    
                    '.$tabla.'
                    </body>
                </html>';

        $tam_hoja = array(0, 0, 612.00, 390.00);//'folio';
        $orientacion='portrait';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($vista)->setPaper($tam_hoja,$orientacion);

        return $pdf->download('reporte_desprendibles_de_pago.pdf');
    }

    /**
     * generar_reporte_desprendibles_de_pago
     *
     */
    public function generar_reporte_desprendibles_de_pago($nom_doc_encabezado_id, $core_tercero_id )
    {        
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);

        $empresa = Empresa::find($documento->core_empresa_id);

        if ( $core_tercero_id == 'Todos') 
        {
            $empleados = $documento->empleados;
        }else{
            $empleados = NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
                                    ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
                                    ->where('nom_contratos.core_tercero_id', $core_tercero_id)
                                    ->select('core_terceros.descripcion AS empleado', 'core_terceros.id AS core_tercero_id', 'nom_cargos.descripcion AS cargo', 'nom_contratos.sueldo AS salario', 'core_terceros.numero_identificacion AS cedula')
                                    ->get();
        }

        $tabla = '';

        foreach ($empleados as $una_persona) 
        {

            $tabla .= '<div class="cuadro">
                            <p style="text-align: center; font-size: 13px; font-weight: bold;">
                                <span style="font-size:14px;">'.$empresa->descripcion.'</span>
                                <br/> 
                                Documento: '.$documento->descripcion.' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Fecha: '.$documento->fecha.'</p>
                            <div style="text-align: center; font-size: 13px; font-weight: bold; width: 100%;"> 
                            Desprendible de pago </div> <br> ';

            $tabla .= '<table style="border: 1px solid; border-collapse: collapse; width:100%;">
                        <tr>
                            <td style="border: 1px solid;"><b>Empleado: </b></td>
                            <td style="border: 1px solid;">'.$una_persona->tercero->descripcion.'</td>
                            <td style="border: 1px solid;">'.Form::TextoMoneda($una_persona->sueldo, 'Sueldo: ').'</td>
                        </tr>
                    </table>
            <table style="width:100%; font-size: 12px;" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="border: 1px solid; text-align:center;"> Conceptos </th>
                            <th style="border: 1px solid; text-align:center;"> Devengo </th>
                            <th style="border: 1px solid; text-align:center;"> Deducción </th>
                        </tr>
                    </thead>
                    <tbody>';

            $registros = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->where('core_tercero_id',$una_persona->core_tercero_id)->orderBy('nom_concepto_id','ASC')->get();


            $total_devengos = 0;
            $total_deducciones = 0;
            foreach ($registros as $un_registro) 
            {
                $concepto = NomConcepto::find($un_registro->nom_concepto_id);

                if ( $un_registro->valor_devengo != 0) 
                {
                    $devengo = Form::TextoMoneda($un_registro->valor_devengo);
                    $deduccion = '';
                }

                if ( $un_registro->valor_deduccion != 0) 
                {
                    $devengo = '';
                    $deduccion = Form::TextoMoneda($un_registro->valor_deduccion);
                }

                $tabla .= '<tr>
                            <td>'.$concepto->descripcion.'</td>
                            <td>'.$devengo.'</td>
                            <td>'.$deduccion.'</td>
                        </tr>';

                $total_devengos += $un_registro->valor_devengo;
                $total_deducciones += $un_registro->valor_deduccion;
            }

            $total_a_pagar = $total_devengos - $total_deducciones;

            $firmas = '<table style="width:100%; font-size: 10px;">
            <tr>
                <td width="20%"> &nbsp; </td>
                <td align="center"> _____________________________ </td>
                <td align="center"> &nbsp;  </td>
                <td align="center"> _____________________________ </td>
                <td width="20%">&nbsp;</td>
            </tr>
            <tr>
                <td width="20%"> &nbsp; </td>
                <td align="center"> Generado por: '.explode("@", $documento->creado_por)[0].' </td>
                <td align="center"> &nbsp;  </td>
                <td align="center"> Recibí conforme <br>
                CC. </td>
                <td width="20%">&nbsp;</td>
            </tr>
        </table>';

            $tabla .='<tr>
                        <td>Totales</td>
                        <td><hr>'.Form::TextoMoneda($total_devengos).'</td>
                        <td><hr>'.Form::TextoMoneda($total_deducciones).'</td>
                    </tr>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                    </tr>
                <tr>
                        <td colspan="3"><b>Saldo a pagar: </b> $'.number_format($total_a_pagar, 0, ',', '.').' ('.NumerosEnLetras::convertir($total_a_pagar,'pesos',false).')</td>
                    </tr>
                </tbody>
            </table> <br/> '.$firmas.'</div> <div class="page-break"></div>';
        }
        return $tabla;
    }
}