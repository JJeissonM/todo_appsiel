<?php

namespace App\Http\Controllers\Contabilidad;
use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use Auth;
use Form;
use View;

use App\Sistema\Aplicacion;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Core\Empresa;

use App\Contabilidad\ContabMovimiento;

class ContabMovimientoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
    	// BOTÓN CREAR CON EL LISTADO DE TRANSACCIONES DEL MODULO
        $tipos_transacciones = Aplicacion::find( Input::get('id') )->tipos_transacciones()->where('sys_tipos_transacciones.estado', 'Activo')->get();

        $id_modelo = 47; // 47 = Documentos contables

        $select_crear = '<div class="dropdown">
            &nbsp;&nbsp;&nbsp;<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="menu1" data-toggle="dropdown"><i class="fa fa-plus"></i> Crear
            <span class="caret"></span></button>
            <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">';
            foreach($tipos_transacciones as $fila) { 
                $select_crear.='<li role="presentation"><a role="menuitem" tabindex="-1" href="'.url('contabilidad/create'.'?id='.Input::get('id').'&id_modelo='.$id_modelo.'&id_transaccion='.$fila->id).'">'.$fila->descripcion.'</a></li>';
            }
        $select_crear.='</ul>
          </div>';

        $miga_pan = [
                ['url'=>'NO','etiqueta'=>'Contabilidad']
            ];

        return view( 'contabilidad.index', compact( 'miga_pan','select_crear' ) );
    }

    // FORMULARIO PARA CREAR UN NUEVO REGISTRO
    public function create()
    {   
        // 
    }


    //     A L M A C E N A R  LOS REGISTROS, esta función se llama desde el método
    // Store del ModeloController
    public static function store(Request $request,$registro_encabezado_doc)
    {
        //
    }


    // FOMRULARIO PARA EDITAR UN REGISTRO
    public function edit($id)
    {
        //
    }

    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {
        //
    }

    // VISTA PARA MOSTRAR UN DOCUMENTO DE TRANSACCION
    public function show($id)
    {
        $reg_anterior = ContabMovimiento::where('id', '<', $id)->max('id');
        $reg_siguiente = ContabMovimiento::where('id', '>', $id)->min('id');

        $view_pdf = ContabMovimientoController::vista_preliminar($id,'show');

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => 'Movimiento contable' ],
                ['url'=>'NO','etiqueta' => 'Consulta' ]
            ];

        return view( 'contabilidad.show_movimiento',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') ); 
    }

    // VISTA PARA MOSTRAR UN DOCUMENTO DE TRANSACCION
    public function imprimir($id)
    {
        $view_pdf = ContabMovimientoController::vista_preliminar($id,'imprimir');
       
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = \App::make('dompdf.wrapper');
        //$pdf->set_option('isRemoteEnabled', TRUE);
        $pdf->loadHTML( $view_pdf )->setPaper($tam_hoja,$orientacion);

        //echo $view_pdf;
        return $pdf->download('documento.pdf');
    }

    // Generar vista para SOHW  o IMPRIMIR
    public static function vista_preliminar($id,$vista)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS documento';

        $encabezado_doc = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
                    ->where('contab_movimientos.id', $id)
                    ->select('core_terceros.descripcion AS tercero','contab_movimientos.fecha',DB::raw($select_raw),'contab_movimientos.detalle_operacion AS detalle','contab_movimientos.documento_soporte','contab_movimientos.core_tipo_transaccion_id','contab_movimientos.core_tipo_doc_app_id','contab_movimientos.id','contab_movimientos.creado_por','contab_movimientos.consecutivo','contab_movimientos.core_empresa_id','contab_movimientos.valor_operacion AS valor_total','core_terceros.numero_identificacion')
                    ->get()[0];

        $tipo_transaccion = TipoTransaccion::find($encabezado_doc->core_tipo_transaccion_id);

        //$core_app = $tipo_transaccion->core_app;

        $tipo_doc_app = TipoDocApp::find($encabezado_doc->core_tipo_doc_app_id);

        $descripcion_transaccion = $tipo_doc_app->descripcion;

        $select_raw4 = 'CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS cuenta';

        // Se crea una tabla con los registros 
        $registros = ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
                            ->where('contab_movimientos.core_empresa_id',Auth::user()->empresa_id)
                            ->where('contab_movimientos.core_tipo_transaccion_id',$encabezado_doc->core_tipo_transaccion_id)
                            ->where('contab_movimientos.core_tipo_doc_app_id',$encabezado_doc->core_tipo_doc_app_id)
                            ->where('contab_movimientos.consecutivo',$encabezado_doc->consecutivo)
                    ->select('core_terceros.descripcion AS tercero',DB::raw($select_raw4),'contab_movimientos.valor_debito','contab_movimientos.valor_credito','contab_movimientos.detalle_operacion')
                            ->get();

        $total_debito=0;
        $total_credito=0;
        $i=0;
        $tabla2 = '<table  class="tabla_registros" style="margin-top: -4px;">
                        <tr>
                            <td colspan="5" align="center">
                               <b>Movimiento contable</b>
                            </td>
                        </tr>
                        <tr class="encabezado">
                            <td>
                               Cuenta
                            </td>
                            <td>
                               Tercero
                            </td>
                            <td>
                               Detalle
                            </td>
                            <td>
                               Débito
                            </td>
                            <td>
                               Crédito
                            </td>
                        </tr>';
        foreach ($registros as $registro) {
            $tabla2.='<tr  class="fila-'.$i.'" >
                            <td>
                               '.$registro->cuenta.'
                            </td>
                            <td>
                               '.$registro->tercero.'
                            </td>
                            <td>
                               '.$registro->detalle_operacion.'
                            </td>
                            <td>
                               $'.number_format($registro->valor_debito, 0, ',', '.').'
                            </td>
                            <td>
                               $'.number_format($registro->valor_credito, 0, ',', '.').'
                            </td>
                        </tr>';
            $i++;
            if ($i==3) {
                $i=1;
            }
            $total_debito+=$registro->valor_debito;
            $total_credito+=$registro->valor_credito;
        }
        $tabla2.='<tr  class="fila-'.$i.'" >
                            <td colspan="3">
                               &nbsp;
                            </td>
                            <td>
                               $'.number_format($total_debito, 0, ',', '.').'
                            </td>
                            <td>
                               $'.number_format($total_credito, 0, ',', '.').'
                            </td>
                        </tr>';
        $tabla2.='</table>';


        $elaboro = $encabezado_doc->creado_por;
        $empresa = Empresa::find($encabezado_doc->core_empresa_id);
        $aplicacion = Aplicacion::find( $tipo_transaccion->core_app_id );

        $view_1 = View::make('contabilidad.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista','aplicacion') )->render();

        $view_2 = View::make('contabilidad.incluir.firmas',compact('elaboro') )->render();


        $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla2.$view_2;

        return $view_pdf;         
    }

}