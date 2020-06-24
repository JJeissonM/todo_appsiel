<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;
use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;


use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Core\Empresa;
use App\Sistema\TipoTransaccion;
use App\Core\Tercero;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Ventas\Cliente;
use App\Ventas\VtasTransaccion;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;

class PedidoController extends TransaccionController
{
    protected $doc_encabezado;

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de ventas
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        if( is_null($this->transaccion) )
        {
            $this->transaccion = TipoTransaccion::find(42);
        }

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(VtasTransaccion::get_datos_tabla_ingreso_lineas_registros($this->transaccion, $motivos));

        return $this->crear($this->app, $this->modelo, $this->transaccion, 'ventas.pedidos.create', $tabla);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if( isset( $request['pedido_web'] ) )
        {
            $request = $this->completar_request( $request );
        }

        $request['fecha_entrega'] = $request['fecha_entrega'] . ' ' . $request['hora_entrega'] . ':00';

        $lineas_registros = json_decode($request->lineas_registros);
        $request['estado'] = "Pendiente";

        // 2do. Crear documento de Ventas
        $ventas_doc_encabezado_id = PedidoController::crear_documento($request, $lineas_registros, $request->url_id_modelo);

        return redirect('vtas_pedidos/' . $ventas_doc_encabezado_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion);
    }

    public function completar_request( $request )
    {
        $request['core_tipo_transaccion_id'] = config( 'ventas.pv_tipo_transaccion_id' );
        $request['core_tipo_doc_app_id'] = config( 'ventas.pv_tipo_doc_app_id' );

        return $request;
    }

    /*
        Crea un documento completo: encabezados y registros
        Devuelve en ID del documento creado
    */
    public function crear_documento(Request $request, array $lineas_registros, $modelo_id)
    {
        $doc_encabezado = $this->crear_encabezado_documento($request, $modelo_id);

        PedidoController::crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        return $doc_encabezado->id;
    }

    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public function crear_registros_documento(Request $request, $doc_encabezado, array $lineas_registros)
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        //dd( $datos );

        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);
        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            // $base_impuesto = $lineas_registros[$i]->precio_unitario / ( 1 + $lineas_registros[$i]->tasa_impuesto / 100 );
            // $valor_total_descuento = $lineas_registros[$i]->precio_unitario * ( 1 + $lineas_registros[$i]->tasa_descuento / 100 ) * $lineas_registros[$i]->cantidad;

            $linea_datos = ['vtas_motivo_id' => $lineas_registros[$i]->inv_motivo_id] +
                ['inv_producto_id' => $lineas_registros[$i]->inv_producto_id] +
                ['precio_unitario' => $lineas_registros[$i]->precio_unitario] +
                ['cantidad' => $lineas_registros[$i]->cantidad] +
                ['precio_total' => $lineas_registros[$i]->precio_total] +
                ['base_impuesto' => $lineas_registros[$i]->base_impuesto] +
                ['tasa_impuesto' => $lineas_registros[$i]->tasa_impuesto] +
                ['valor_impuesto' => $lineas_registros[$i]->valor_impuesto] +
                ['base_impuesto_total' => $lineas_registros[$i]->base_impuesto_total] +
                [ 'tasa_descuento' => (float)$lineas_registros[$i]->tasa_descuento ] +
                [ 'valor_total_descuento' => (float)$lineas_registros[$i]->valor_total_descuento ] +
                ['creado_por' => Auth::user()->email] +
                ['estado' => 'Activo'];


            VtasDocRegistro::create(
                $datos +
                    ['vtas_doc_encabezado_id' => $doc_encabezado->id] +
                    $linea_datos
            );

            $total_documento += $lineas_registros[$i]->precio_total;
        } // Fin por cada registro

    }


    /**
     * Mostrar las EXISTENCIAS de una bodega ($id).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id);
        $this->doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);
        $doc_registros = VtasDocRegistro::get_registros_impresion($this->doc_encabezado->id);
        $this->empresa = Empresa::find($this->doc_encabezado->core_empresa_id);
        $resolucion = '';
        $doc_encabezado = $this->doc_encabezado;
        $empresa = $this->empresa;
        //$documento_vista = $this->generar_documento_vista($id, 'documento_vista');
        $documento_vista = "";
        $id_transaccion = $this->transaccion->id;
        $registros_contabilidad = [];
        $cliente = Cliente::find($doc_encabezado->cliente_id);
        $miga_pan = [
            ['url' => 'ventas?id=' . Input::get('id'), 'etiqueta' => 'Ventas'],
            ['url' => 'web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'), 'etiqueta' => $this->modelo->descripcion],
            ['url' => 'NO', 'etiqueta' => $this->doc_encabezado->documento_transaccion_prefijo_consecutivo]
        ];

        return view('ventas.pedidos.show', compact('id', 'cliente', 'doc_registros', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan', 'doc_encabezado', 'registros_contabilidad', 'empresa'));
    }

    /*
        Imprimir
    */
    public function imprimir($id)
    {
        $documento_vista = $this->generar_documento_vista($id, 'documento_imprimir');

        // Se prepara el PDF
        $orientacion = 'portrait';
        $tam_hoja = array(0, 0, 50, 800); //'A4';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($documento_vista); //->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream($this->doc_encabezado->documento_transaccion_descripcion . ' - ' . $this->doc_encabezado->documento_transaccion_prefijo_consecutivo . '.pdf');
    }

    /*
        Enviar por email
    */
    public function enviar_por_email($id)
    {
        $documento_vista = $this->generar_documento_vista($id, 'documento_imprimir');

        $tercero = Tercero::find($this->doc_encabezado->core_tercero_id);

        $asunto = $this->doc_encabezado->documento_transaccion_descripcion . ' No. ' . $this->doc_encabezado->documento_transaccion_prefijo_consecutivo;

        $cuerpo_mensaje = 'Saludos, <br/> Le hacemos llegar su ' . $asunto;

        $vec = EmailController::enviar_por_email_documento($this->empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $documento_vista);

        return redirect('vtas_pedidos/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with($vec['tipo_mensaje'], $vec['texto_mensaje']);
    }


    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista($id, $nombre_vista)
    {
        $this->doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        $doc_registros = VtasDocRegistro::get_registros_impresion($this->doc_encabezado->id);

        $this->empresa = Empresa::find($this->doc_encabezado->core_empresa_id);

        $resolucion = '';

        $doc_encabezado = $this->doc_encabezado;
        $empresa = $this->empresa;

        return View::make('ventas.pedidos.' . $nombre_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion'))->render();
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $general = new ModeloController();
        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);
        $registros = VtasDocRegistro::get_registros_impresion($registro->id);

        $lista_campos = $general->get_campos_modelo($modelo, $registro, 'edit');

        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion, $tipo_transaccion, $lista_campos, $cantidad_campos, 'create', null);

        $tercero = Tercero::find($registro->core_tercero_id);
        $registro->cliente_input = $tercero->apellido1 . " " . $tercero->apellido2 . " " . $tercero->nombre1 . " " . $tercero->otros_nombres;

        $registro->inv_bodega_id = 1;

        $form_create = [
            'url' => $modelo->url_form_create,
            'campos' => $lista_campos
        ];


        $body = View::make('ventas.incluir.lineas_registros', compact('registro', 'registros'))->render();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        $miga_pan = [
            ['url' => 'ventas?id=' . Input::get('id'), 'etiqueta' => 'Ventas'],
            ['url' => 'NO', 'etiqueta' => $tipo_transaccion->descripcion]
        ];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(VtasTransaccion::get_datos_tabla_ingreso_lineas_registros($tipo_transaccion, $motivos, $body));

        return view('ventas.pedidos.edit', compact('form_create', 'id_transaccion', 'miga_pan', 'tabla', 'registro', 'registros'));
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

    /*
        Proceso de eliminar PEDIDO
        Se eliminan los registros de:
            - se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public static function anular_pedido($id)
    {
        $pedido = VtasDocEncabezado::find($id);

        VtasDocRegistro::where('vtas_doc_encabezado_id', $pedido->id)->update(['estado' => 'Anulado']);

        $pedido->update(['estado' => 'Anulado']);

        return redirect('vtas_pedidos/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Pedido ANULADO correctamente.');
    }

    //Crea remision a partir del pedido
    public function remision(Request $request)
    {
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('ventas');

        // Modelo del encabezado del documento
        $rm_modelo_id = $parametros['rm_modelo_id'];
        $rm_tipo_transaccion_id = $parametros['rm_tipo_transaccion_id'];
        $rm_tipo_doc_app_id = $parametros['rm_tipo_doc_app_id'];

        $lineas_registros = json_decode($request->lineas_registros);

        // Se crea el documento, se cambia temporalmente el tipo de transacción y el tipo_doc_app
        $tipo_transaccion_id_original = $request['core_tipo_transaccion_id'];
        $core_tipo_doc_app_id_original = $request['core_tipo_doc_app_id'];

        $request['core_tipo_transaccion_id'] = $rm_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $rm_tipo_doc_app_id;
        $request['estado'] = 'Pendiente';
        $request['consecutivo'] = "";
        $hoy = getdate();
        $request['fecha'] = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $remision_creada_id = InventarioController::crear_documento($request, $lineas_registros, $rm_modelo_id);
        $pedido = VtasDocEncabezado::get_registro_impresion($request->id);
        $pedido->remision_doc_encabezado_id = $remision_creada_id;
        $pedido->estado = "Cumplido";
        $pedido->save();
        return redirect('inventarios/' . $remision_creada_id . '?id=' . $request->url_id . '&id_modelo=' . $rm_modelo_id . '&id_transaccion=' . $rm_tipo_transaccion_id);
    }
}

/*

    array:30 [▼
  "_token" => "lroJ4zP16GeE1dyO0iL2qe2NAvd0UQYHbSCYZfdv"
  "core_empresa_id" => "1"
  "core_tipo_doc_app_id" => "41"
  "fecha" => "2020-05-27"
  "cliente_input" => " ANA  LOEZ PEREZ (8.789) "
  "descripcion" => ""
  "fecha_entrega" => "2020-05-27"
  "core_tipo_transaccion_id" => "42"
  "consecutivo" => ""
  "url_id" => "13"
  "url_id_modelo" => "175"
  "url_id_transaccion" => "42"
  "inv_bodega_id_aux" => ""
  "vendedor_id" => "1"
  "forma_pago" => "forma_pago"
  "fecha_vencimiento" => "fecha_vencimiento"
  "inv_bodega_id" => "1"
  "cliente_id" => "1"
  "zona_id" => "1"
  "clase_cliente_id" => "1"
  "equipo_ventas_id" => "1"
  "core_tercero_id" => "1"
  "lista_precios_id" => "1"
  "lista_descuentos_id" => "1"
  "liquida_impuestos" => "1"
  "lineas_registros" => "[{"inv_motivo_id":"10","inv_bodega_id":"1","inv_producto_id":"1","costo_unitario":"1e-7","precio_unitario":"1200","base_impuesto":"1008.4033613445379","tasa_impuesto":"19","valor_impuesto":"191.59663865546213","base_impuesto_total":"12100.840336134454","cantidad":"12","costo_total":"0.0000012","precio_total":"14400","tasa_descuento":"0","valor_total_descuento":"0","Producto":"1 1 YOGURT","Motivo":"Ventas POS","Stock":"0","Cantidad":"12","Precio Unit. (IVA incluido)":"$ 1.200","Dcto. (%)":"[object Object]","Dcto. Tot. ($)":"$ 0","IVA":"19%","Total":"$ 14.400"}]"
  "tipo_transaccion" => "factura_directa"
  "rm_tipo_transaccion_id" => "24"
  "dvc_tipo_transaccion_id" => "34"
  "saldo_original" => "0"
]


*/