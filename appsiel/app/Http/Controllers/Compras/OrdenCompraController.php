<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;


use Spatie\Permission\Models\Permission;

use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;

// Modelos
use App\Inventarios\InvProducto;

use App\Compras\ComprasTransaccion;
use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\OrdenCompra;
use App\Compras\Proveedor;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Inventarios\InvDocEncabezado;
use App\Sistema\Html\BotonesAnteriorSiguiente;
use Illuminate\Support\Facades\Auth as FacadesAuth;

use App\Contabilidad\Impuesto;

class OrdenCompraController extends TransaccionController
{
    /**
     * Muestra formulario para la creación de un nuevo registro
     */
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['11-entrada' => 'Compras nacionales'];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(ComprasTransaccion::get_datos_tabla_ingreso_lineas_registros($this->transaccion, $motivos));

        return $this->crear($this->app, $this->modelo, $this->transaccion, 'compras.orden_compras.create', $tabla);
    }

    /**
     * Almacenar un nuevo registro
     */
    public function store(Request $request)
    {
        // 1ro. No tiene documento de ENTRADA de inventarios
        $request['entrada_almacen_id'] = 0;

        // 2do. Crear encabezado del documento
        $request['estado'] = 'Pendiente';
        $doc_encabezado = CrudController::crear_nuevo_registro($request, $request->url_id_modelo); // Nuevo encabezado

        // 3ro. Crear líneas de registros del documento
        OrdenCompraController::crear_lineas_registros($request, $doc_encabezado);

        return redirect('orden_compra/' . $doc_encabezado->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion);
    }

    public static function crear_lineas_registros(Request $request, $doc_encabezado)
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        $lineas_registros = json_decode($request->lineas_registros);

        $total_documento = 0;
        // Por cada entrada de almacén pendiente
        $cantidad_registros = count($lineas_registros);
        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            $cantidad = (float) $lineas_registros[$i]->cantidad;
            $total_base_impuesto = (float) $lineas_registros[$i]->costo_total;

            $tasa_impuesto = Impuesto::get_tasa( (int) $lineas_registros[$i]->inv_producto_id, $request->proveedor_id, 0 );

            $precio_unitario = (float) $lineas_registros[$i]->costo_unitario * ( 1 + $tasa_impuesto  / 100 );

            $precio_total = $precio_unitario * $cantidad;

            $tasa_descuento = 0;
            $valor_total_descuento = 0;
            if ( isset( $lineas_registros[$i]->tasa_descuento ) )
            {
                $tasa_descuento = $lineas_registros[$i]->tasa_descuento;
                $valor_total_descuento = $lineas_registros[$i]->valor_total_descuento;
            }

            $linea_datos = ['inv_bodega_id' => (int) $lineas_registros[$i]->inv_bodega_id] +
                            ['inv_motivo_id' => (int) $lineas_registros[$i]->inv_motivo_id] +
                            ['inv_producto_id' => (int) $lineas_registros[$i]->inv_producto_id] +
                            ['precio_unitario' => $precio_unitario] +
                            ['cantidad' => $cantidad] +
                            ['precio_total' => $precio_total] +
                            ['base_impuesto' =>  $total_base_impuesto] +
                            ['tasa_impuesto' => $tasa_impuesto ] +
                            ['valor_impuesto' => (abs($precio_total) - $total_base_impuesto)] +
                            [ 'tasa_descuento' => $tasa_descuento ] +
                            [ 'valor_total_descuento' => $valor_total_descuento ] +
                            ['creado_por' => Auth::user()->email] +
                            ['estado' => 'Activo'];

            ComprasDocRegistro::create(
                $datos +
                    ['compras_doc_encabezado_id' => $doc_encabezado->id] +
                    $linea_datos
            );

            $total_documento += $precio_total;
        }

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();
    } 



    public function show($id)
    {
        $this->set_variables_globales();
        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id);
        $doc_encabezado = ComprasDocEncabezado::get_registro_impresion($id);
        $entrada=$enlace = null;
        if ($doc_encabezado->entrada_almacen_id > 0) {
            $entrada = InvDocEncabezado::get_registro_impresion($doc_encabezado->entrada_almacen_id);
        }
        $docs_relacionados = ComprasDocEncabezado::get_documentos_relacionados($doc_encabezado);
        $doc_registros = ComprasDocRegistro::get_registros_impresion($doc_encabezado->id);
        $empresa = $this->empresa;
        $documento_vista = '';
        $id_transaccion = $doc_encabezado->core_tipo_transaccion_id;
        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo);
        $url_crear = $this->modelo->url_crear . $this->variables_url;
        $proveedor = Proveedor::find($doc_encabezado->proveedor_id);
        $vista = 'compras.orden_compras.show';
        return view($vista, compact('id', 'entrada', 'proveedor', 'docs_relacionados', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan', 'doc_encabezado', 'doc_registros', 'empresa', 'url_crear'));
    }


    /*
    /    Permite crear una entrada de almacen a partir de la orden de compra
    */
    public function entrada_almacen(Request $request)
    {
        //Modifico la orden
        $orden = OrdenCompra::find($request->id);
        $orden->estado = "Cumplida";
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('compras');

        // Modelo del encabezado del documento
        $ea_modelo_id = $parametros['ea_modelo_id'];
        $ea_tipo_transaccion_id = $parametros['ea_tipo_transaccion_id'];
        $ea_tipo_doc_app_id = $parametros['ea_tipo_doc_app_id'];

        $lineas_registros = json_decode($request->lineas_registros);

        $request['core_tipo_transaccion_id'] = $ea_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $ea_tipo_doc_app_id;
        $request['estado'] = 'Pendiente';
        $request['core_empresa_id'] = $orden->core_empresa_id;
        $request['consecutivo'] = "";
        $request['core_tercero_id'] = $orden->core_tercero_id;
        $request['descripcion'] = $orden->descripcion;
        $hoy = getdate();
        $request['fecha'] = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $entrada_almacen_id = InventarioController::crear_documento($request, $lineas_registros, $ea_modelo_id);
        $orden->entrada_almacen_id = $entrada_almacen_id;
        $orden->save();
        return redirect('inventarios/' . $entrada_almacen_id . '?id=' . $request->url_id . '&id_modelo=' . $ea_modelo_id . '&id_transaccion=' . $ea_tipo_transaccion_id);
    }
}
