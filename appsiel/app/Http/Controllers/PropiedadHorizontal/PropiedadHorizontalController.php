<?php

namespace App\Http\Controllers\PropiedadHorizontal;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Input;
use DB;

use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;
use App\Core\TipoDocApp;
use App\Core\Tercero;

use App\Core\Empresa;

use App\CxC\CxcDocRegistro;
use App\CxC\CxcMovimiento;
use App\CxC\CxcServicio;
use App\CxC\CxcEstadoCartera;

use App\PropiedadHorizontal\Propiedad;


use App\Contabilidad\ContabMovimiento;

class PropiedadHorizontalController extends Controller
{
    protected $datos = [];

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
                ['url'=>'NO','etiqueta'=>'Propiedad Horizontal']
            ];

        return view('propiedad_horizontal.index',compact('miga_pan'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    /**
     * Muestra el formulario para la Generación de CxC  
     *
     */
    public function generar_cxc()
    {
        //$empresas = Empresa::all();

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        //print_r($tipo_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = [
                ['url'=>'propiedad_horizontal?id='.Input::get('id'),'etiqueta'=>'Propiedad Horizontal'],
                ['url'=>'NO','etiqueta'=>$tipo_transaccion->descripcion]
            ];

        return view('propiedad_horizontal.generar_cxc', compact('form_create','id_transaccion','miga_pan') );//,'empresas'
    }



    /**
     * PETICIÓN AJAX
     * Almacena la Generación de CxC.
     */
    public function store(Request $request)
    {
        
        $modelo = Modelo::find($request->url_id_modelo);

        // obtener cualquier registro del modelo, para obtener la table de ese modelo
        $any_registro = New $modelo->name_space;
        $nombre_tabla = $any_registro->getTable();

        // LLamar a los campos del modelo para verificar los que son requeridos
        $lista_campos = $modelo->campos->toArray();
        for ($i=0; $i < count($lista_campos); $i++) { 
            if ($lista_campos[$i]['requerido']) {
                $this->validate($request,[$lista_campos[$i]['name']=>'required']);
            }
            if ($lista_campos[$i]['unico']) {
                $this->validate($request,[$lista_campos[$i]['name']=>'unique:'.$nombre_tabla]);
            }
        }

        // SELECCIONAR LAS PROPIEDADES (Inmuebles) ASOCIADAS A LA EMPRESA ENVIADA
        $propiedades = Propiedad::get_propiedades($request->core_empresa_id);

        $tbody = '';
        $precio_total=0;
        $i=0;
        $cant_propiedades = 0;
        $primer_registro = 0;
        // POR CADA PROPIEDAD
        foreach ( $propiedades as $propiedad ) { 

            $cant_propiedades++;

            // 1. SE CREA EL ENCABEZADO DEL DOCUMENTO (DocumentoCxC)
            // 1.1. Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id,$request->core_tipo_doc_app_id) + 1;

            // Se obtiene el primer documento generado para la impresión por lotes
            if ($primer_registro==0) {
                $primer_registro = $consecutivo;
            }

            // 1.2. incementamos el consecutivo para ese tipo de documento y empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id,$request->core_tipo_doc_app_id);

            // 1.3. Se REEMPLAZA el consecutivo en los datos del request
            // Tambien se adiciona el codigo_referencia_tercero
            $this->datos = array_merge($request->all(),['consecutivo' => $consecutivo, 'codigo_referencia_tercero' => $propiedad['id'], 'tipo_movimiento' => 'Facturación masiva' ]);
            
            // 1.4. Se guarda el encabezado del documento 
            $propiedad_id = $propiedad['id'];
            $core_tercero_id = $propiedad['core_tercero_id'];

            $cxc_doc_encabezado = app($modelo->name_space)->create( $this->datos +  
                            ['core_tercero_id'=>$core_tercero_id] );

            
            // 2. SE CREAN LOS REGISTROS DEL DOCUMENTO Y EL MOVIMIENTO DE CARTERA
            // 2.1. Se van hayando las variables de los campos que se almacenarán en LOS REGISTROS Y  el movimiento
            
            $cxc_motivo_id = 1; // 1 = Generación masiva CxC Prop. Horizontal
            $valor_cartera = 0;

            // Se verifica si el inmueble tiene un Vlr. de cuota de administración por defecto
            // Si no lo tiene se usa el concepto asignado por defecto
            if ( (float)$propiedad['valor_cuota_defecto'] > 0 ) {
                $cxc_servicio_id = 0;
                $precio_venta = (float)$propiedad['valor_cuota_defecto'];
                $detalle_operacion = 'Cuota de administración - '.$request->descripcion;
            }else{
                $servicio_default = CxcServicio::find($propiedad['cxc_servicio_id']);
                $cxc_servicio_id = $servicio_default->id;
                $precio_venta = $servicio_default->precio_venta;
                $detalle_operacion = $servicio_default->descripcion.' - '.$request->descripcion;
            }
                

            CxcDocRegistro::create(
                            [ 'cxc_doc_encabezado_id' => $cxc_doc_encabezado->id ] +
                            [ 'cxc_motivo_id' => $cxc_motivo_id ] + 
                            [ 'cxc_servicio_id' => $cxc_servicio_id ] + 
                            [ 'valor_unitario' => $precio_venta ] + 
                            [ 'cantidad' => 1 ] +
                            [ 'valor_total' => $precio_venta ] +
                            [ 'descripcion' => $detalle_operacion ] +
                            [ 'estado' => 'Activo' ] );
            
            $valor_cartera+=$precio_venta;

            /*
                **  Generar la contabilidad (Cartera vs Ingresos)
            */
            // CARTERA (DB)
            $contab_cuenta = Tercero::find($core_tercero_id)->cuenta_cartera;
            $contab_cuenta_id = $contab_cuenta->id; 

            $valor_debito = $precio_venta;

            $valor_credito = 0;

            $this->contabilizar_registro( $core_tercero_id, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

            // INGRESOS (CR)
            if ( isset($servicio_default) ) {
                $contab_cuenta_id = $servicio_default->contab_cuenta_id;
            }else{
                $contab_cuenta_id = $propiedad['cuenta_ingresos_id'];
            }
                
            $valor_debito = 0;
            $valor_credito = $precio_venta;
            $this->contabilizar_registro( $core_tercero_id, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);


            // Se obtienen los servicios ADICIONALES asociados para cobrar
            $servicios = DB::table('ph_propiedad_tiene_servicios')
                            ->where( 'propiedad_id', $propiedad['id'] )
                            ->get();
            // Se debe recorrer cada servicio asignado a la propiedad
            foreach ($servicios as $un_servicio) {
                $sql_servicio = DB::table('cxc_servicios')->where('id',$un_servicio->cxc_servicio_id)->get();
                $el_servicio = $sql_servicio[0];

                if ( $un_servicio->valor_servicio == 0) 
                {
                    $precio_venta = $el_servicio->precio_venta;
                }else{
                    $precio_venta = $un_servicio->valor_servicio;
                }

                $detalle_operacion = $el_servicio->descripcion.' - '.$request->descripcion;
                // Se crea cada registro en la tabla cxc_doc_registros
                CxcDocRegistro::create(
                            [ 'cxc_doc_encabezado_id' => $cxc_doc_encabezado->id ] +
                            [ 'cxc_motivo_id' => $cxc_motivo_id ] + 
                            [ 'cxc_servicio_id' => $el_servicio->id ] + 
                            [ 'valor_unitario' => $precio_venta ] + 
                            [ 'cantidad' => 1 ] +
                            [ 'valor_total' => $precio_venta ] +
                            [ 'descripcion' => $detalle_operacion ] +
                            [ 'estado' => 'Activo' ] );

                $valor_cartera+=$precio_venta;

                /*
                    **  Generar la contabilidad (Cartera vs Ingresos)
                */
                // CARTERA (DB)
                $contab_cuenta = Tercero::find($core_tercero_id)->cuenta_cartera;
                $contab_cuenta_id = $contab_cuenta->id; 

                $valor_debito = $precio_venta;

                $valor_credito = 0;

                $this->contabilizar_registro( $core_tercero_id,$contab_cuenta_id,$detalle_operacion,$valor_debito,$valor_credito);

                // INGRESOS (CR)
                $contab_cuenta_id = $el_servicio->contab_cuenta_id;
                $valor_debito = 0;
                $valor_credito = $precio_venta;
                $this->contabilizar_registro( $core_tercero_id,$contab_cuenta_id,$detalle_operacion,$valor_debito,$valor_credito);


                // Se elimina el servicio asignado a la propiedad
                DB::table('ph_propiedad_tiene_servicios')
                        ->where( 'propiedad_id', $propiedad['id'] )
                        ->where( 'cxc_servicio_id', $el_servicio->id )
                        ->delete();
            } // Fin for cada servicio asignado a la propiedad

            // 2.2. Se almacena el registro del movimiento en la tabla cxc_movimientos
            $this->datos = array_merge($this->datos,['descripcion' => 'Cobro de servicios '.$request->descripcion]);
            

            $cxc_movimiento = CxcMovimiento::create( $this->datos + 
                            [ 'core_tercero_id' => $core_tercero_id ] +  
                            [ 'valor_cartera' => $valor_cartera ] +
                            [ 'detalle_operacion' => 'Facturación - '.$request->descripcion ]+
                            [ 'estado' => 'Pendiente' ] );

            // Se agrega un nuevo estado de cartera para el movimiento creado
            CxcEstadoCartera::crear($cxc_movimiento->id, $request->fecha, 0, $valor_cartera, 'Pendiente', $request->creado_por, $request->modificado_por);

            // Se actualiza el valor total en el encabezado del documento
            $cxc_doc_encabezado->valor_total = $valor_cartera;
            $cxc_doc_encabezado->save();

            $precio_total+=$valor_cartera;
            $tipo_doc_app = TipoDocApp::find($request->core_tipo_doc_app_id);
            $documento  = $tipo_doc_app->prefijo.' '.$consecutivo;

            $tbody.='<tr>
                    <td>'.$propiedad['codigo'].'</td>
                    <td>'.$propiedad['descripcion'].'</td>
                    <td> <a href="'.url('cxc/'.$cxc_doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo).'" target="_blank" title="Vista previa">'.$documento.'</a> </td>
                    <td>'.number_format($valor_cartera, 0, ',', '.').'</td>
                </tr>';
        } // Termina bucle "Para cada propiedad"


        $thead = '<tr>
                        <th>Propiedad</th>
                        <th>Propietario</th>
                        <th>Documento</th>
                        <th>Valor total</th>
                    </tr>';

        $mensaje = '<div class="alert alert-success">
  <strong>¡Transacción exitosa!</strong> Cuentas de cobro creadas correctamente.
</div>';

        return [$thead, $tbody, number_format($precio_total, 0, ',', '.'), $cant_propiedades, $mensaje, $request->core_empresa_id, $request->core_tipo_doc_app_id, $primer_registro, $consecutivo];
    }

    /**
     * 
     */
    public function generar_consulta_preliminar_cxc(Request $request)
    {
       $propiedades = Propiedad::get_propiedades($request->core_empresa_id);

        $tbody = '';
        $precio_total=0;
        $i=0;
        $cant_propiedades = 0;
        foreach ($propiedades as $propiedad) { 

            // SERVICIO DEFAULT
            // Se verifica si el inmueble tiene un Vlr. de cuota de administración por defecto
            // Si no lo tiene se usa el concepto asignado por defecto
            if ( (float)$propiedad['valor_cuota_defecto'] > 0 ) 
            {
                $cxc_servicio_id = 0;
                $precio_venta = (float)$propiedad['valor_cuota_defecto'];
                $detalle_operacion = 'Cuota de administración - '.$request->descripcion;
            }else{
                $servicio_default = CxcServicio::find($propiedad['cxc_servicio_id']);
                $cxc_servicio_id = $servicio_default->id;
                $precio_venta = $servicio_default->precio_venta;
                $detalle_operacion = $servicio_default->descripcion.' - '.$request->descripcion;
            }

            $tbody.='<tr>
                        <td>'.$propiedad['codigo'].'</td>
                        <td>'.$propiedad['descripcion'].'</td>
                        <td>'.$detalle_operacion.'</td>
                        <td>$'.number_format($precio_venta, 0, ',', '.').'</td>
                        <td>1</td>
                        <td>$'.number_format($precio_venta, 0, ',', '.').'</td>
                    </tr>';
            $precio_total+=$precio_venta;
            $i++;

            // Por cada servicio asociado
            $servicios = DB::table('ph_propiedad_tiene_servicios')->where('propiedad_id',$propiedad['id'])->get();
            //$servicios = $propiedad->servicios();
            foreach ($servicios as $un_servicio) 
            {
                $sql_servicio = DB::table('cxc_servicios')->where('id',$un_servicio->cxc_servicio_id)->get();
                $el_servicio = $sql_servicio[0];

                if ( $un_servicio->valor_servicio == 0) 
                {
                    $precio_venta = $el_servicio->precio_venta;
                }else{
                    $precio_venta = $un_servicio->valor_servicio;
                }

                $tbody.='<tr>
                            <td>'.$propiedad['codigo'].'</td>
                            <td>'.$propiedad['descripcion'].'</td>
                            <td>'.$el_servicio->descripcion.' '.$request->descripcion.'</td>
                            <td>$'.number_format($precio_venta, 0, ',', '.').'</td>
                            <td>1</td>
                            <td>$'.number_format($precio_venta, 0, ',', '.').'</td>
                        </tr>';
                $precio_total+=$precio_venta;
                $i++;
            }
                
            $cant_propiedades++;
        }

        $tbody.='<tr>
                <td colspan="5"></td>
                <td>$'.number_format($precio_total, 0, ',', '.').'</td>
            </tr>';

        return [$tbody,number_format($precio_total, 0, ',', '.'),$i,$cant_propiedades];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function contabilizar_registro($core_tercero_id,$contab_cuenta_id,$detalle_operacion,$valor_debito,$valor_credito)
    {
        ContabMovimiento::create( $this->datos +
                            [ 'core_tercero_id' => $core_tercero_id ] + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ]
                        );
    }
}