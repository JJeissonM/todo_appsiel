<?php

namespace App\Http\Controllers\Contabilidad;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Contabilidad\ContabilidadController;

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
use App\Contabilidad\ContabPeriodoEjercicio;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

class ProcesosController extends Controller
{
    public function generar_listado_cierre_ejercicio( Request $request )
    {
        $array_clases_cuentas = [ 4, 5, 6, 7];
        $periodo_ejercicio = ContabPeriodoEjercicio::find( $request->periodo_ejercicio_id );
        $lista_movimientos = [];
        foreach ($array_clases_cuentas as $key => $clase_cuenta_id )
        {
            $movimiento_clase_cuenta = ContabMovimiento::get_saldo_movimiento_clase_cuenta($periodo_ejercicio->fecha_desde, $periodo_ejercicio->fecha_hasta, $clase_cuenta_id  );
            foreach ( $movimiento_clase_cuenta as $mov_cuenta )
            {
                $lista_movimientos[] = $mov_cuenta;
            }
        }

        $cuenta_ganancias_perdidas_ejercicio = ContabCuenta::find( (int)config('contabilidad.cuenta_ganancias_perdidas_ejercicio') );

        $vista = View::make( 'contabilidad.procesos.cierre_ejercicio_tabla_saldos_cuentas_resultados', compact( 'lista_movimientos', 'cuenta_ganancias_perdidas_ejercicio', 'periodo_ejercicio' ) )->render();

        return $vista;
    }

    public function crear_nota_cierre_ejercicio( Request $request )
    {
        $array_clases_cuentas = [ 4, 5, 6, 7];
        $periodo_ejercicio = ContabPeriodoEjercicio::find( $request->periodo_ejercicio_id2 );
        $cuenta_ganancias_perdidas_ejercicio_id = (int)config('contabilidad.cuenta_ganancias_perdidas_ejercicio');

        $lista_movimientos = [];
        $valor_total = 0;
        $tabla_registros_documento = '[';
        $es_primera_linea = true;
        foreach ($array_clases_cuentas as $key => $clase_cuenta_id )
        {
            $movimiento_clase_cuenta = ContabMovimiento::get_saldo_movimiento_clase_cuenta($periodo_ejercicio->fecha_desde, $periodo_ejercicio->fecha_hasta, $clase_cuenta_id  );
            foreach ( $movimiento_clase_cuenta as $mov_cuenta )
            {
                $saldo = $mov_cuenta->valor_saldo;

                if( $saldo == 0 )
                {
                    continue;
                }

                $valor_db_cta_resultado = 0;
                $valor_cr_cta_resultado = $saldo;
                $valor_db_cta_cierre = $saldo;
                $valor_cr_cta_cierre = 0;
                if( $saldo < 0 ) // Saldo CR
                {
                    $valor_db_cta_resultado = $saldo * -1;
                    $valor_cr_cta_resultado = 0;
                    $valor_db_cta_cierre = 0;
                    $valor_cr_cta_cierre = $saldo * -1;
                }

                if ( !$es_primera_linea )
                {
                    $tabla_registros_documento .= ',';
                }

                $tabla_registros_documento .= '{"fecha_vencimiento":"'.$periodo_ejercicio->fecha_hasta.'","documento_soporte_tercero":"","tipo_transaccion":"causacion","Cuenta":"' . $mov_cuenta->cuenta->id . '- COD_CUENTA DESCRIPCION_CUENTA","Tercero":"-","Detalle":"","debito":"$  ' . $valor_db_cta_resultado . '","credito":"$  ' . $valor_cr_cta_resultado . '"},{"fecha_vencimiento":"2021-03-25","documento_soporte_tercero":"","tipo_transaccion":"causacion","Cuenta":"' . $cuenta_ganancias_perdidas_ejercicio_id . '- COD_CUENTA DESCRIPCION_CUENTA ","Tercero":"-","Detalle":"","debito":"$  ' . $valor_db_cta_cierre . '","credito":"$  ' . $valor_cr_cta_cierre . '"}';

                $es_primera_linea = false;
                $valor_total += $mov_cuenta->valor_saldo;
            }
        }

        $tabla_registros_documento .= ',{"fecha_vencimiento":"","documento_soporte_tercero":"","tipo_transaccion":"NA","Cuenta":"NA","Tercero":"NA","Detalle":"NA","debito":"NA","credito":"NA"},{"fecha_vencimiento":"","documento_soporte_tercero":"","tipo_transaccion":"","Cuenta":"NA","Tercero":"NA","Detalle":"NA"}]';

        $request["tabla_registros_documento"] = $tabla_registros_documento;
        $request["core_empresa_id"] = Auth::user()->empresa_id;
        $request["core_tipo_doc_app_id"] = (int)config('contabilidad.tipo_documento_cierre_ejercicio');
        $request["fecha"] = $periodo_ejercicio->fecha_hasta;
        $request["core_tercero_id_aux"] = " NOMBRE_TERCERO ";
        $request["core_tercero_id"] = (int)config('contabilidad.tercero_default_cierre_ejercicio');
        $request["documento_soporte"] = "";
        $request["descripcion"] = "Cierre del ejercicio contable " . $periodo_ejercicio->descripcion . '. Cancelación cuentas de resultado.';
        $request["creado_por"] = Auth::user()->email;
        $request["estado"] = "Activo";
        $request["consecutivo"] = "";
        $request["core_tipo_transaccion_id"] = (int)config('contabilidad.transaccion_default_cierre_ejercicio');
        $request["valor_total"] = abs($valor_total);
        $request["modificado_por"] = "0";
        $request["url_id"] = "14";
        $request["url_id_modelo"] = "47";
        $request["url_id_transaccion"] = (int)config('contabilidad.transaccion_default_cierre_ejercicio');
        $request["inv_bodega_id_aux"] = "";

        $contab_controller = new ContabilidadController();

        $registro_encabezado_doc = $contab_controller->crear_encabezado_documento($request, $request->url_id_modelo);

        $tabla_registros_documento = json_decode($request->tabla_registros_documento);
        
        $contab_controller->almacenar_lineas_registros( $request, $tabla_registros_documento, $registro_encabezado_doc );

        return redirect( 'contabilidad/'.$registro_encabezado_doc->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }
}