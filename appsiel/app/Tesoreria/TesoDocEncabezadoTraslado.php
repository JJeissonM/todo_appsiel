<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;

use App\Contabilidad\ContabMovimiento;

class TesoDocEncabezadoTraslado extends Model
{
    // Apunta a la misma tabla del modelo de Recaudos
    protected $table = 'teso_doc_encabezados';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'teso_tipo_motivo', 'documento_soporte', 'descripcion', 'teso_medio_recaudo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'valor_total', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Fecha', 'Tercero', 'Detalle', 'Valor total', 'Estado'];

    public $vistas = '{"create":"tesoreria.traslados_efectivo.create"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $transaccion_id = 43;
        return TesoDocEncabezadoRecaudo::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('teso_doc_encabezados.core_tipo_transaccion_id', $transaccion_id)
            ->select(
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo1'),
                'teso_doc_encabezados.fecha AS campo2',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'teso_doc_encabezados.descripcion AS campo4',
                'teso_doc_encabezados.valor_total AS campo5',
                'teso_doc_encabezados.estado AS campo6',
                'teso_doc_encabezados.id AS campo7'
            )
            ->where(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('teso_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $transaccion_id = 43;
        $string = TesoDocEncabezadoRecaudo::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('teso_doc_encabezados.core_tipo_transaccion_id', $transaccion_id)
            ->select(
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'teso_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS TERCERO'),
                'teso_doc_encabezados.descripcion AS DETALLE',
                'teso_doc_encabezados.valor_total AS VALOR_TOTAL',
                'teso_doc_encabezados.estado AS ESTADO'
            )
            ->where(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('teso_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TRASLADOS DE EFECTIVO";
    }


    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento';

        return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.id', $id)
            ->select(DB::raw($select_raw), 'teso_doc_encabezados.fecha', 'core_terceros.descripcion AS tercero', 'teso_doc_encabezados.descripcion AS detalle', 'teso_doc_encabezados.documento_soporte', 'teso_doc_encabezados.core_tipo_transaccion_id', 'teso_doc_encabezados.core_tipo_doc_app_id', 'teso_doc_encabezados.id', 'teso_doc_encabezados.creado_por', 'teso_doc_encabezados.consecutivo', 'teso_doc_encabezados.core_empresa_id', 'teso_doc_encabezados.core_tercero_id', 'teso_doc_encabezados.teso_tipo_motivo')
            ->get()[0];
    }

    public function store_adicional($datos, $registro)
    {
        $datos['consecutivo'] = $registro->consecutivo;

        $registros = json_decode($datos['lineas_registros']);
        $total = 0;
        foreach ($registros as $item) {
            $motivo = explode('-', $item->teso_motivo_id);
            $aux = TesoMotivo::where([['teso_tipo_motivo', 'Traslado'], ['movimiento', $motivo[0]]])->first();
            $medio_recaudo = explode('-', $item->teso_medio_recaudo_id);
            $caja = explode('-', $item->teso_caja_id);
            $cuenta = explode('-', $item->teso_cuenta_bancaria_id);
            $valor = explode('$', $item->valor);

            $teso_registro = new TesoDocRegistro();
            $teso_registro->teso_encabezado_id = $registro->id;
            $teso_registro->teso_motivo_id = $aux->id;
            $teso_registro->teso_cuenta_bancaria_id = $cuenta[0];
            $teso_registro->core_tercero_id = $registro->core_tercero_id;
            if ($medio_recaudo[1] != 'Tarjeta bancaria') {
                $teso_registro->teso_caja_id = $caja[0];
                $teso_registro->teso_cuenta_bancaria_id = 0;
            } else {
                $teso_registro->teso_caja_id = 0;
                $teso_registro->teso_cuenta_bancaria_id = $cuenta[0];
            }
            $teso_registro->teso_medio_recaudo_id = $medio_recaudo[0];
            $teso_registro->valor = $valor[1];
            $teso_registro->estado = 'Activo';
            $teso_registro->detalle_operacion = 0;
            $total = $total + abs($teso_registro->valor);
            $result = $teso_registro->save();
            if ($result) {
                $movimiento = new TesoMovimiento();
                $movimiento->fecha = $registro->fecha;
                $movimiento->core_empresa_id = $registro->core_empresa_id;
                $movimiento->core_tercero_id = $registro->core_tercero_id;
                $movimiento->core_tipo_transaccion_id = $registro->core_tipo_transaccion_id;
                $movimiento->core_tipo_doc_app_id = $registro->core_tipo_doc_app_id;
                $movimiento->teso_motivo_id = $teso_registro->teso_motivo_id;
                $movimiento->teso_caja_id = $teso_registro->teso_caja_id;
                $movimiento->teso_cuenta_bancaria_id = $teso_registro->teso_cuenta_bancaria_id;
                $movimiento->valor_movimiento = $teso_registro->valor;
                $movimiento->consecutivo = $registro->consecutivo;
                $movimiento->estado = 'Activo';
                $movimiento->creado_por = $registro->creado_por;
                $movimiento->save();
            }



            /*
                **  Determinar la cuenta contable DB (CAJA O BANCOS)
            */
            if ($teso_registro->teso_caja_id != 0) {
                $sql_contab_cuenta_id = TesoCaja::find($teso_registro->teso_caja_id);
                $contab_cuenta_id = $sql_contab_cuenta_id->contab_cuenta_id;
            }

            if ($teso_registro->teso_cuenta_bancaria_id != 0) {
                $sql_contab_cuenta_id = TesoCuentaBancaria::find($teso_registro->teso_cuenta_bancaria_id);
                $contab_cuenta_id = $sql_contab_cuenta_id->contab_cuenta_id;
            }

            $detalle_operacion = $datos['descripcion'];
            $valor_debito = $teso_registro->valor;
            $valor_credito = 0;

            $this->contabilizar_registro($datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_registro->teso_caja_id, $teso_registro->teso_cuenta_bancaria_id);

            // Como los motivos se ingresaron al momento de registrar cada medio de pago,
            // Si es un Anticipo u Otro Recaudo se contabiliza la contrapartida de cada motivo Inmediatamente
            /*
                **  Determinar la cuenta contable desde el motivo
            */
            $motivo = TesoMotivo::find($teso_registro->teso_motivo_id);
            $contab_cuenta_id = $motivo->contab_cuenta_id;

            $valor_debito = 0;
            $valor_credito = $teso_registro->valor;

            $this->contabilizar_registro($datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
        }

        $registro->valor_total = $total / 2;
        $registro->estado = 'Activo';
        $registro->save();

        return redirect('web' . '?id=' . $datos['url_id'] . '&id_modelo=' . $datos['url_id_modelo'])->with('flash_message', 'Registro CREADO correctamente.');
    }



    public function contabilizar_registro($datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0)
    {
        ContabMovimiento::create(
            $datos +
                ['contab_cuenta_id' => $contab_cuenta_id] +
                ['detalle_operacion' => $detalle_operacion] +
                ['valor_debito' => $valor_debito] +
                ['valor_credito' => ($valor_credito * -1)] +
                ['valor_saldo' => ($valor_debito - $valor_credito)] +
                ['teso_caja_id' => $teso_caja_id] +
                ['teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id]
        );
    }
}
