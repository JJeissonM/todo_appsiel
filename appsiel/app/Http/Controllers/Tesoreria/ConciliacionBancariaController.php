<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use DB;

use Input;
use Form;

// Modelos

use App\Matriculas\Estudiante;

use App\Core\Tercero;

use App\Tesoreria\TesoPlanPagosEstudiante;

use App\Matriculas\Matricula;


class ConciliacionBancariaController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'NO','etiqueta'=>'Conciliación bancaria']
            ];

        return view('tesoreria.conciliacion_bancaria', compact('miga_pan'));
    }


    public function procesa_archivo_plano_bancos(Request $request)
    {
        $lineas = file( $request->archivo );

        $vista = '';

        foreach ($lineas as $num_linea => $linea) {
            if ($num_linea!=0) {
                $vec_fila = explode(',',$linea);

                // codigo_transaccion = OFICINA  CAJERO  ESTACION    SESION  SECUENCIA  FECHA_
                $codigo_transaccion = $vec_fila[4].$vec_fila[5].$vec_fila[6].$vec_fila[7].$vec_fila[8].str_replace("/","",$vec_fila[13]);

                $ciudad = $vec_fila[9];
                $valor_transaccion = $vec_fila[12];
                $fecha_transaccion = $vec_fila[13];
                
                $cod_1 = substr($vec_fila[17], 0, 5);
                $cod_2 = substr($vec_fila[17], 5);
                $cod_2 = str_repeat("0", 2-strlen($cod_2)).$cod_2;

                $codigo_referencia_tercero = $cod_1."-".$cod_2;

                $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS estudiante';

                $cartera_estudiante = TesoPlanPagosEstudiante::leftJoin('matriculas','matriculas.id_estudiante','=','teso_cartera_estudiantes.id_estudiante')->leftJoin('sga_estudiantes','matriculas.id_estudiante','=','sga_estudiantes.id')->leftJoin('sga_cursos','matriculas.curso_id','=','sga_cursos.id')->where('matriculas.codigo',$codigo_referencia_tercero)->where('matriculas.estado','Activo')->where('teso_cartera_estudiantes.valor_cartera','=',$valor_transaccion)->select(DB::raw($select_raw),'sga_cursos.descripcion AS curso','teso_cartera_estudiantes.valor_cartera','teso_cartera_estudiantes.inv_producto_id','teso_cartera_estudiantes.fecha_vencimiento','teso_cartera_estudiantes.id AS id_cartera','teso_cartera_estudiantes.estado')->get()->toArray();

                $tabla = '<div style="border: 2px solid red;"><h3> Línea # '.$vec_fila[20].' del archivo cargado</h3><table class="table table-striped" style="margin-top: -4px;">
                        <thead>
                            <th>  </th>
                            <th> Estudiante </th>
                            <th> Curso </th>
                            <th> Cod. matrícula </th>
                            <th> Concepto </th>
                            <th> Mes </th>
                            <th> Estado </th>
                            <th> Fecha consignación </th>
                            <th> Valor consignación </th>
                            <th> Acción </th>
                        </thead>';

                if ( count($cartera_estudiante) > 0) {
                    $i=1;
                    foreach ($cartera_estudiante as $fila) 
                    {
                        $id_modelo = 31;
                        if($fila['estado']!='Pagada'){
                            $btn = '<a class="btn btn-primary btn-xs btn-detail" href="'.url('tesoreria/hacer_recaudo_cartera/'.$fila['id_cartera'].'?id='.Input::get('id').'&id_modelo='.$id_modelo.'&fecha_transaccion='.$fecha_transaccion).'" title="Recaudar" target="_blank"><i class="fa fa-btn fa-cube"></i>&nbsp;Recaudar</a>';
                        }else{
                            $btn = '<a class="btn btn-info btn-xs btn-detail" href="'.url('tesoreria/imprimir_comprobante_recaudo/'.$fila['id_cartera']).'"><i class="fa fa-btn fa-print"></i>&nbsp;Imprimir comprobante</a>';
                        }
                        
                        $fecha = explode("-", $fila["fecha_vencimiento"] );
                        $mes = Form::NombreMes( [$fecha[1]] );

                        $tabla.=' <tr>
                            <td> '.$i.' </td>
                            <td> '.$fila["estudiante"].' </td>
                            <td> '.$fila["curso"].' </td>
                            <td> '.$codigo_referencia_tercero.'
                            </td>
                            <td> '.$fila["concepto"].'
                            </td>
                            <td> '.$mes.'
                            </td>
                            <td> '.$fila["estado"].'
                            </td>
                            <td> '.$fecha_transaccion.' 
                            </td>
                            <td> '.$valor_transaccion.'
                            </td>
                            <td>'.$btn.'</td>
                        </tr>';
                        $i++;
                    }

                    $tabla.=' </table></div>';
                    

                }else{

                    $tabla = '<div class="alert alert-danger">
                          <strong><h3> Línea # '.$vec_fila[20].' del archivo cargado</h3>
                          </strong> El código '.$codigo_referencia_tercero.' no está matriculado o no tiene libreta de pagos creada en el sistema.
                        </div>';
                    
                }

                $vista.=$tabla."</br>";
            }
        }

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'NO','etiqueta'=>'Conciliación bancaria']
            ];

        return view('tesoreria.conciliacion_bancaria_resultado', compact('vista','miga_pan'));
    }    
}