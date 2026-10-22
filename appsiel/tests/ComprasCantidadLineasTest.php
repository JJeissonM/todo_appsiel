<?php

use App\Compras\Services\CantidadLineasService;
use Illuminate\Http\Request;

class ComprasCantidadLineasTest extends TestCase
{
    public function test_cantidad_oculta_impone_uno_y_totales_unitarios_sin_recalcular_retencion()
    {
        config(['compras.ocultar_cantidad_lineas'=>1]);
        foreach ([null, 0, 9] as $cantidad) {
            $request = new Request(['lineas_registros'=>json_encode([(object)[
                'inv_producto_id'=>123, 'cantidad'=>$cantidad, 'precio_unitario'=>119,
                'costo_unitario'=>90, 'tasa_descuento'=>10, 'tasa_impuesto'=>19,
                'precio_total'=>999, 'costo_total'=>999, 'valor_retencion'=>2.25,
            ]])]);
            CantidadLineasService::prepararRequest($request);
            $linea = json_decode($request->lineas_registros)[0];
            $this->assertEquals(1, $linea->cantidad);
            $this->assertEquals(107.1, $linea->precio_total);
            $this->assertEquals(90, $linea->base_impuesto);
            $this->assertEquals(90, $linea->costo_total);
            $this->assertEquals(17.1, $linea->valor_impuesto, '', 0.00001);
            $this->assertEquals(2.25, $linea->valor_retencion);
        }
    }

    public function test_config_desactivada_conserva_datos_y_entradas_existentes_no_se_modifican()
    {
        $json = '[{"inv_producto_id":1,"cantidad":8,"precio_total":800}]';
        foreach ([false, 0, '0', 'false'] as $config) {
            config(['compras.ocultar_cantidad_lineas'=>$config]);
            $request = new Request(['lineas_registros'=>$json]);
            CantidadLineasService::prepararRequest($request);
            $this->assertSame($json, $request->lineas_registros);
        }
        config(['compras.ocultar_cantidad_lineas'=>1]);
        $request = new Request(['lineas_registros'=>'[{"id_doc":7}]']);
        CantidadLineasService::prepararRequest($request);
        $this->assertSame('[{"id_doc":7}]', $request->lineas_registros);
    }

    public function test_tabla_oculta_ambas_columnas_sin_eliminar_campos()
    {
        config(['compras.ocultar_cantidad_lineas'=>1, 'compras.maneja_retenciones_fuente'=>0]);
        $tipo = (object)['id'=>25, 'modelo_registros_documentos'=>'App\\Compras\\ComprasDocRegistro'];
        foreach ([false, true] as $ocultarMotivo) {
            $datos = App\Compras\ComprasTransaccion::get_datos_tabla_ingreso_lineas_registros($tipo, ['1-entrada'=>'Compra'], ['ocultar_columna_motivo'=>$ocultarMotivo]);
            $html = view('layouts.elementos.tabla_ingreso_lineas_registros', compact('datos'))->render();
            $this->assertContains('data-ocultar-cantidad="1"', $html);
            $this->assertContains('id="cantidad"', $html);
            $this->assertContains('id="existencia_actual"', $html);
            $this->assertEquals(2, substr_count($html, 'display: none !important'));
        }
    }
}
