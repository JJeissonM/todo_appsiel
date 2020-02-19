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
use App\Sistema\Modelo;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Core\Tercero;
use App\Core\Empresa;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;

class ContabilidadController extends Controller
{
    protected $datos = [];
    protected $grupos_cuentas = [];

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
        // SE REPITE TODO LO DE ModeloController@create

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>$tipo_transaccion->descripcion]
            ];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = '';

        return view( 'contabilidad.create', compact( 'form_create','id_transaccion','miga_pan', 'tabla' ) );
    }


    //     A L M A C E N A R  LOS REGISTROS (Ya se llenó el encabezado), esta función se llama desde el método store del ModeloController
    public function store(Request $request,$registro_encabezado_doc)
    {
        // Ya se llenó la tabla *_doc_encabezados* en el ModeloController

        $tabla_registros_documento = json_decode($request->tabla_registros_documento);

        // 1ro. se guardan los registros asociados al encabezado del documento
        // Se recorre la tabla enviada en el request, descartando las DOS últimas filas
        for ($i=0; $i < count($tabla_registros_documento)-2; $i++) {
            // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 
            $vec_1 = explode("-", $tabla_registros_documento[$i]->Cuenta);
            $contab_cuenta_id = $vec_1[0];

            $vec_2 = explode("-", $tabla_registros_documento[$i]->Tercero);
            $core_tercero_id = $vec_2[0];
            if ($core_tercero_id == '') {
                $core_tercero_id = $request->core_tercero_id;
            }

            //dd($core_tercero_id);

            $detalle_operacion = $tabla_registros_documento[$i]->Detalle;

            // Se les quita la etiqueta de signo peso a los textos monetarios recibidos
            // en la tabla de movimiento
            $valor_debito = substr($tabla_registros_documento[$i]->debito, 1);
            $valor_credito = substr($tabla_registros_documento[$i]->credito, 1);

            ContabDocRegistro::create(
                            [ 'contab_doc_encabezado_id' => $registro_encabezado_doc->id ] + 
                            [ 'contab_cuenta_id' => (int)$contab_cuenta_id ] + 
                            [ 'core_tercero_id' => (int)$core_tercero_id ] + 
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => (float)$valor_debito] + 
                            [ 'valor_credito' => (float)$valor_credito]
                        );


            // 1.1. Para cada registro del documento, también se va actualizando el movimiento de contabilidad
            
            // Para el movimiento contable se guarda en detalle_operacion el detalle del encabezado del documento
            if ($detalle_operacion == '') {
                $detalle_operacion = $request->descripcion;
            }

            $this->datos = array_merge( $request->all(), ['core_tercero_id' => $core_tercero_id , 'consecutivo' => $registro_encabezado_doc->consecutivo] );

            ContabilidadController::contabilizar_registro( $this->datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

        }

        // se llama la vista de RecaudoController@show
        return redirect( 'contabilidad/'.$registro_encabezado_doc->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo );
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
        $reg_anterior = ContabDocEncabezado::where('id', '<', $id)->where('core_empresa_id', Auth::user()->empresa_id)->max('id');
        $reg_siguiente = ContabDocEncabezado::where('id', '>', $id)->where('core_empresa_id', Auth::user()->empresa_id)->min('id');

        $view_pdf = ContabilidadController::vista_preliminar($id,'show');

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => 'Documentos contables' ],
                ['url'=>'NO','etiqueta' => 'Consulta' ]
            ];

        return view( 'contabilidad.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') ); 
    }

    // VISTA PARA MOSTRAR UN DOCUMENTO DE TRANSACCION
    public function imprimir($id)
    {
        $view_pdf = ContabilidadController::vista_preliminar($id,'imprimir');
       
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
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS documento';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero';

        $encabezado_doc = ContabDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_encabezados.core_tercero_id')
                    ->where('contab_doc_encabezados.id', $id)
                    ->select(DB::raw($select_raw),'contab_doc_encabezados.fecha',DB::raw($select_raw2),'contab_doc_encabezados.descripcion AS detalle','contab_doc_encabezados.documento_soporte','contab_doc_encabezados.core_tipo_transaccion_id','contab_doc_encabezados.core_tipo_doc_app_id','contab_doc_encabezados.id','contab_doc_encabezados.creado_por','contab_doc_encabezados.consecutivo','contab_doc_encabezados.core_empresa_id','contab_doc_encabezados.valor_total','core_terceros.numero_identificacion')
                    ->get()[0];

        $tipo_transaccion = TipoTransaccion::find($encabezado_doc->core_tipo_transaccion_id);

        //$core_app = $tipo_transaccion->core_app;

        $tipo_doc_app = TipoDocApp::find($encabezado_doc->core_tipo_doc_app_id);

        $descripcion_transaccion = $tipo_doc_app->descripcion;

        $select_raw3 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero';

        $select_raw4 = 'CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS cuenta';

        // Se crea una tabla con los registros de medios de recaudos
        $registros = ContabDocRegistro::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_doc_registros.contab_cuenta_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_registros.core_tercero_id')
                            ->where('contab_doc_registros.contab_doc_encabezado_id',$encabezado_doc->id)
                            ->select(DB::raw($select_raw3),DB::raw($select_raw4),'contab_doc_registros.valor_debito','contab_doc_registros.valor_credito','contab_doc_registros.detalle_operacion')
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
                               $'.number_format($registro->valor_debito, 2, ',', '.').'
                            </td>
                            <td>
                               $'.number_format($registro->valor_credito, 2, ',', '.').'
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
                               $'.number_format($total_debito, 2, ',', '.').'
                            </td>
                            <td>
                               $'.number_format($total_credito, 2, ',', '.').'
                            </td>
                        </tr>';
        $tabla2.='</table>';


        $elaboro = $encabezado_doc->creado_por;
        $empresa = Empresa::find($encabezado_doc->core_empresa_id);

        $view_1 = View::make('contabilidad.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista') )->render();

        $view_2 = View::make('contabilidad.incluir.firmas',compact('elaboro') )->render();


        $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla2.$view_2;

        return $view_pdf;         
    }

    //
    // AJAX: enviar fila para el ingreso de registros al elaborar documento contable
    public static function contab_get_fila( $id_fila )
    {
        $registros = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('codigo')->get();
        $cuentas[''] = '';
        foreach ($registros as $fila) {
            $cuentas[$fila->id]=$fila->codigo." ".$fila->descripcion; 
        }

        $registros_2 = Tercero::where('core_empresa_id','=',Auth::user()->empresa_id )->get();
        $terceros[''] = '';
        foreach ($registros_2 as $fila2) {
            $terceros[$fila2->id]=$fila2->numero_identificacion." ".$fila2->descripcion; 
        }

        $btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
        $btn_confirmar = "<button type='button' class='btn btn-success btn-xs btn_confirmar'><i class='glyphicon glyphicon-ok'></i></button>";

        $tr = '<tr>
                    <td>
                        '.Form::select( 'campo_cuentas', $cuentas, null, [ 'id' => 'combobox_cuentas', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td>
                        '.Form::select( 'campo_terceros', $terceros, null, [ 'id' => 'combobox_terceros', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td> '.Form::text( 'detalle', null, [ 'id' => 'col_detalle', 'class' => 'caja_texto' ] ).' </td>
                    <td> '.Form::text( 'debito', null, [ 'id' => 'col_debito', 'class' => 'caja_texto' ] ).' </td>
                    <td> '.Form::text( 'credito', null, [ 'id' => 'col_credito', 'class' => 'caja_texto' ] ).' </td>
                    <td>'.$btn_confirmar.$btn_borrar.'</td>
                </tr>';

        return $tr;
    }

    public function get_saldo_grupo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$grupo_cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }

    public function get_saldo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.fecha','<', $fecha_ini)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_movimientos.contab_cuenta_id','=', $cuenta_id )
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ) )
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.id','=',$cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }

    // los valores de $valor_debito y $valor_credito deben venir en valor absoluto
    public static function contabilizar_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0)
    {
        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id]
                        );
    }

    public function contab_get_grupos_cuentas($clase_id)
    {
        $registros_c = DB::table('contab_cuenta_grupos')
                ->where( [ 
                    [ 'contab_cuenta_clase_id', $clase_id ],
                    [ 'core_empresa_id','=', Auth::user()->empresa_id ]
                    ] )
                ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros_c as $campo) {
            $grupo = DB::table('contab_cuenta_grupos')
                ->where( 'id', $campo->grupo_padre_id )
                ->value('descripcion');
            $opciones .= '<option value="'.$campo->id.'">'.$grupo.' > '.$campo->descripcion.'</option>';
        }

        return $opciones;
    }
    
    // Parámetro enviados por GET
    public function consultar_cuentas()
    {
        $campo_busqueda = Input::get('campo_busqueda');
        
        switch ( $campo_busqueda ) 
        {
            case 'descripcion':
                $operador = 'LIKE';
                $texto_busqueda = '%'.Input::get('texto_busqueda').'%';
                break;
            case 'codigo':
                $operador = 'LIKE';
                $texto_busqueda = Input::get('texto_busqueda').'%';
                break;
            
            default:
                # code...
                break;
        }

        $datos = ContabCuenta::where('contab_cuentas.estado','Activo')->where('contab_cuentas.core_empresa_id',Auth::user()->empresa_id)->where('contab_cuentas.core_app_id','0')->where('contab_cuentas.'.$campo_busqueda,$operador,$texto_busqueda)->select('contab_cuentas.id AS cuenta_id','contab_cuentas.descripcion','contab_cuentas.codigo')->get()->take(7);

        //dd($datos);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        foreach ($datos as $linea) 
        {
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
            }

            $html .= '<a class="list-group-item list-group-item-autocompletar '.$clase.'" data-tipo_campo="cuenta" data-cuenta_id="'.$linea->cuenta_id.
                                '" data-id="'.$linea->cuenta_id.
                                '" > '.$linea->codigo.' '.$linea->descripcion.'</a>';
        }
        $html .= '</div>';

        return $html;
    }


    public function corregir_signo_a_movimientos()
    {
        $movimiento = ContabMovimiento::all();

        $i = 1;
        foreach ($movimiento as $registro)
        {
            $valor_debito = abs($registro->valor_debito);
            $valor_credito = abs($registro->valor_credito) * -1;
            $valor_saldo = $valor_debito + $valor_credito;

            $registro->valor_debito = $valor_debito;
            $registro->valor_credito = $valor_credito;
            $registro->valor_saldo = $valor_saldo;
            $registro->save();
            echo $i.'  ';
            $i++;
        }

        echo '<br>Se actualizaron '.($i-1).' registros.';
    }
}