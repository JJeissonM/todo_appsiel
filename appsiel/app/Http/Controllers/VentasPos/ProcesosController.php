<?php

namespace App\Http\Controllers\VentasPos;

use App\CxC\CxcAbono;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Sistema\Modelo;
use App\VentasPos\FacturaPos;

use App\Ventas\VtasMovimiento;
use App\VentasPos\DocRegistro;
use App\VentasPos\Movimiento;
use App\VentasPos\Services\AccountingServices;
use App\VentasPos\Services\SalesServices;
use Illuminate\Support\Facades\Input;

class ProcesosController extends Controller
{
    public function reconstruir_mov_ventas_documento($documento_id)
    {
        $result = $this->reconstruir_movimiento_ventas_un_documento($documento_id);

        if($result->status=='error')
        {
            return redirect( 'pos_factura/' . $documento_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('mensaje_error', 'Factura en estado Pendiente. Aún no tiene movimiento de ventas.');
        }

        return redirect( 'pos_factura/' . $documento_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('flash_message', 'Movimiento de ventas actualizado.');
    }

    public function reconstruir_movimiento_ventas_un_documento($documento_id)
    {
        $documento = FacturaPos::find($documento_id);

        if ($documento->estado == 'Pendiente') {
            return (object)['status'=>'error'];
        }

        // Eliminar movimientos actuales
        Movimiento::where([
            ['core_tipo_transaccion_id','=', $documento->core_tipo_transaccion_id],
            ['core_tipo_doc_app_id','=', $documento->core_tipo_doc_app_id],
            ['consecutivo','=', $documento->consecutivo]
        ])
        ->delete();

        VtasMovimiento::where([
                ['core_tipo_transaccion_id','=', $documento->core_tipo_transaccion_id],
                ['core_tipo_doc_app_id','=', $documento->core_tipo_doc_app_id],
                ['consecutivo','=', $documento->consecutivo]
            ])
            ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = $documento->lineas_registros;
        
        $datos = $documento->toArray();
        $total_documento = 0;
        $datos['zona_id'] = $documento->cliente->zona_id;
        $datos['clase_cliente_id'] = $documento->cliente->clase_cliente_id;
        $datos['equipo_ventas_id'] = $documento->cliente->vendedor->equipo_ventas_id; 
        foreach ($registros_documento as $linea)
        {
            VtasMovimiento::create( 
                $datos +
                $linea->toArray()
            );            

            Movimiento::create(
                $datos +
                $linea->toArray()
            );

            $total_documento += $linea->precio_total;
        }

        $documento->valor_total = $total_documento;
        $documento->save();

        return (object)['status'=>'success'];
    }

    public function form_modificar_total_factura($documento_id)
    {
        $doc_encabezado = FacturaPos::get_registro_impresion($documento_id);        
        
        $modelo = Modelo::find( Input::get('id_modelo') );
        $lista_campos = ModeloController::get_campos_modelo($modelo, $doc_encabezado, 'edit');

        $cantidad = count($lista_campos);

        array_unshift($lista_campos, [
            "id" => 201,
            "descripcion" => "Empresa",
            "tipo" => "personalizado",
            "name" => "encabezado",
            "opciones" => "",
            "value" => '<div style="border: solid 1px #ddd; padding-top: -20px;">
                                                            <b style="font-size: 1.2em; text-align: center; display: block;">
                                                                ' . $doc_encabezado->documento_transaccion_descripcion . '
                                                                <br/>
                                                                <b>No.</b> ' . $doc_encabezado->documento_transaccion_prefijo_consecutivo . '
                                                            </b>
                                                        </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        foreach ($lista_campos as $key => $value) {
            if( in_array( $value['name'], ['core_empresa_id','core_tipo_doc_app_id', 'forma_pago', 'fecha_vencimiento', 'inv_bodega_id', 'separador1','consecutivo','core_tipo_transaccion_id', 'cliente_input'] ))
            {
                unset( $lista_campos[$key] );
            }
        }

        array_push($lista_campos, [
            "id" => 201,
            "descripcion" => "Cliente",
            "tipo" => "select",
            "name" => "cliente_id",
            "opciones" => "model_App\Ventas\Cliente",
            "value" => $doc_encabezado->cliente_id,
            "atributos" => ["class"=>"combobox"],
            "definicion" => "",
            "requerido" => 1,
            "editable" => 1,
            "unico" => 0
        ]);

        array_push($lista_campos, [
            "id" => 201,
            "descripcion" => "Total factura",
            "tipo" => "bsText",
            "name" => "valor_total",
            "opciones" => "",
            "value" => $doc_encabezado->valor_total,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 1,
            "editable" => 1,
            "unico" => 0
        ]);

        $form_create = [
            'url' => url('vtas_pos_store_nuevo_total_factura'),
            'campos' => $lista_campos
        ];

        return view('ventas_pos.form_modificar_total_factura', compact('form_create','documento_id') );
    }

    public function store_nuevo_total_factura(Request $request)
    {
        $doc_encabezado = FacturaPos::find( $request->documento_id );

        $registros_documento = DocRegistro::where('vtas_pos_doc_encabezado_id', $doc_encabezado->id)->get();

        foreach ($registros_documento as $linea)
        {
            $participacion = $request->valor_total / $linea->precio_total;

            $linea->precio_unitario *= $participacion;
            $linea->precio_total *= $participacion;
            $linea->base_impuesto *= $participacion;
            $linea->valor_impuesto *= $participacion;
            $linea->base_impuesto_total *= $participacion;
            $linea->valor_total_descuento *= $participacion;

            $linea->save();
        }

        
        $doc_encabezado->valor_total = $request->valor_total;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->cliente_id = $request->cliente_id;
        $doc_encabezado->fecha = $request->fecha;

        $doc_encabezado->save();

        (new AccountingServices())->recontabilizar_factura( $request->documento_id );
        
        return redirect('pos_factura/' . $request->documento_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Factura de ventas modificada correctamente.');
        

        /**
         * 
         * PENDIENTE DE AQUI PARA ABAJO
         */


        $array_wheres = [
            'core_empresa_id' => $doc_encabezado->core_empresa_id,
            'core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $doc_encabezado->core_tipo_doc_app_id,
            'consecutivo' => $doc_encabezado->consecutivo
        ];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxcAbono::where('doc_cxc_transacc_id', $doc_encabezado->core_tipo_transaccion_id)
            ->where('doc_cxc_tipo_doc_id', $doc_encabezado->core_tipo_doc_app_id)
            ->where('doc_cxc_consecutivo', $doc_encabezado->consecutivo)
            ->count();

        if ($cantidad != 0) {
            return redirect('pos_factura/' . $request->documento_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('mensaje_error', 'Factura NO puede ser modificada. Se le han hecho Recaudos de CXC (Tesorería).');
        }

        // Modificar lineas de registros del documento

        // Modificar movimiento de ventas

        
        // Modificar movimiento de CxC

        
        // Modificar movimiento de Tesoreria


        // Modificar movimientos contables



        
    }
}