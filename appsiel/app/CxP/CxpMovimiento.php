<?php

namespace App\CxP;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxpMovimiento extends Model
{
  //protected $table = '';

  protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'core_empresa_id', 'core_tercero_id', 'modelo_referencia_tercero_index', 'referencia_tercero_id', 'doc_proveedor_prefijo', 'doc_proveedor_consecutivo', 'fecha', 'fecha_vencimiento', 'valor_documento', 'valor_pagado', 'saldo_pendiente', 'creado_por', 'modificado_por', 'estado'];

  public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Proveedor', 'Documento', 'Fecha', 'Valor documento', 'Valor pagado', 'Saldo pendiente', 'Estado'];

  public $urls_acciones = '{
                              "create":"web/create",
                              "store":"compras_registro_cxp",
                              "update":"compras_registro_cxp/id_fila"
                          }';


  public function actualizar_saldos($abono)
  {

    // Se diminuye el saldo_pendiente en el documento pendiente, si saldo_pendiente == 0 se elimina el registro
    $nuevo_saldo = $this->saldo_pendiente - $abono; //valor_documento

    $nuevo_valor_pagado = $this->valor_pagado + $abono;
    $this->valor_pagado = $nuevo_valor_pagado;
    $this->saldo_pendiente = $nuevo_saldo;

    if ( $nuevo_saldo == 0)
    {
      $this->estado = 'Pagado';
      $this->modificado_por = Auth::user()->email;
    }

    $this->save();
  }

  public static function consultar_registros($nro_registros)
  {
    return CxpMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_movimientos.core_tipo_doc_app_id')
      ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_movimientos.core_tercero_id')
      ->where('cxp_movimientos.core_empresa_id', Auth::user()->empresa_id)
      ->select(
        DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo1'),
        DB::raw('CONCAT(cxp_movimientos.doc_proveedor_prefijo," ",cxp_movimientos.doc_proveedor_consecutivo) AS campo2'),
        'cxp_movimientos.fecha AS campo3',
        'cxp_movimientos.valor_documento AS campo4',
        'cxp_movimientos.valor_pagado AS campo5',
        'cxp_movimientos.saldo_pendiente AS campo6',
        'cxp_movimientos.estado AS campo7',
        'cxp_movimientos.id AS campo8'
      )
      ->orderBy('cxp_movimientos.created_at', 'DESC')
      ->paginate($nro_registros);
  }


  public static function crear($datos, $valor_cartera, $estado, $detalle_operacion)
  {
    return CxpMovimiento::create($datos +
      ['valor_cartera' => $valor_cartera] +
      ['saldo_pendiente' => $valor_cartera] +
      ['estado' => $estado] +
      ['detalle_operacion' => $detalle_operacion]);
  }

  /* MUESTRA TOTAL CARTERA, DESCONTANDO ANTICIPOS*/
  public static function estados_de_cuentas_resumido($fecha_inicial, $fecha_final, $estado, $codigo_referencia_tercero, $operador, $core_tercero_id)
  {
    $select_raw = 'sum(cxp_movimientos.valor_cartera) AS saldo_pendiente';

    $movimiento_cxc = CxpMovimiento::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_movimientos.core_tercero_id')
      ->where('cxp_movimientos.fecha', '<=', $fecha_final)
      ->where('cxp_movimientos.core_empresa_id', Auth::user()->empresa_id)
      ->where('cxp_movimientos.codigo_referencia_tercero', $operador, $codigo_referencia_tercero)
      ->groupBy('cxp_movimientos.codigo_referencia_tercero')
      ->orderBy('cxp_movimientos.codigo_referencia_tercero')
      ->select(DB::raw($select_raw), 'core_terceros.descripcion AS tercero', 'cxp_movimientos.codigo_referencia_tercero')
      ->get()
      ->toArray();

    return $movimiento_cxc;
  }

  public static function get_documentos_tercero($core_tercero_id, $fecha)
  {
    $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_movimientos.consecutivo) AS documento';

    return CxpMovimiento::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_movimientos.core_tercero_id')
      ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_movimientos.core_tipo_doc_app_id')
      ->where('cxp_movimientos.core_empresa_id', Auth::user()->empresa_id)
      ->where('cxp_movimientos.core_tercero_id', '=', $core_tercero_id)
      ->where('cxp_movimientos.saldo_pendiente', '<>', 0)
      ->where('cxp_movimientos.fecha', '<=', $fecha)
      ->select(
        'cxp_movimientos.id',
        'cxp_movimientos.core_tipo_transaccion_id',
        'cxp_movimientos.core_tipo_doc_app_id',
        'cxp_movimientos.consecutivo',
        'core_terceros.descripcion AS tercero',
        DB::raw($select_raw),
        'cxp_movimientos.fecha',
        'cxp_movimientos.fecha_vencimiento',
        'cxp_movimientos.valor_documento',
        'cxp_movimientos.valor_pagado',
        'cxp_movimientos.saldo_pendiente'
      )
      ->orderBy('cxp_movimientos.fecha')
      ->get()->toArray();
  }

  public static function actualizar_valores_doc_cxp($doc_encabezado, $abono)
  {
    //   -3.000  =          -12.000                 -     -9.000
    $nuevo_saldo = $doc_encabezado->saldo_pendiente - $abono; //valor_documento

    //    -9.000        =          0                    + -9.000
    $nuevo_valor_pagado = $doc_encabezado->valor_pagado + $abono;

    $doc_encabezado->valor_pagado = $nuevo_valor_pagado; // -9.000
    $doc_encabezado->saldo_pendiente = $nuevo_saldo; // -3.000
    $doc_encabezado->save();

    if ($nuevo_saldo == 0) {
      $doc_encabezado->update(
        [
          'estado' => 'Pagado',
          'modificado_por' => Auth::user()->email
        ]
      );
    }
  }
}
