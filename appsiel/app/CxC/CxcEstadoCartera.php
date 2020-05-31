<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Sistema\TipoTransaccion;

class CxcEstadoCartera extends Model
{
  protected $table = 'cxc_estados_cartera';

  protected $fillable = ['cxc_movimiento_id', 'fecha_registro','valor_pagado','saldo_pendiente','estado','creado_por','modificado_por'];

  /* 
    ** Crear un registro para el estado de un documento de cartera
    ** Se verifica si el documento ya tiene un registro de estado anterior, si lo tiene se crea el nuevo con base en los datos del ultimo registro creado
  */
  public static function crear($cxc_movimiento_id, $fecha_registro, $valor_pagado)
  {
    // El documento_cartera_id, se puede repetir si se genera desde distintas aplicaciones, ventas, tesoreria, gestión de cobros, etc. Por eso siempre hay que validar también la transacción origen del documento 
    $estado_documento = CxcEstadoCartera::where('cxc_movimiento_id', $cxc_movimiento_id)->orderBy('fecha_registro','DESC')->first();

      // Registro por primera vez
      $nuevo_valor_pagado = 0;
      $nuevo_saldo_pendiente = $valor_pagado;

    if ( !is_null($estado_documento) ) 
    {
      // El campo valor_pagado va acumulando el valor de los pagos hechos al documento de cartera
      $nuevo_valor_pagado = $estado_documento->valor_pagado + $valor_pagado;
      $nuevo_saldo_pendiente = $estado_documento->saldo_pendiente - $valor_pagado;
    }

      $estado = 'Pendiente';
    if ( $nuevo_saldo_pendiente == 0) 
    {
      $estado = 'Pagado';
    }

    CxcEstadoCartera::create( [ 'cxc_movimiento_id' => $cxc_movimiento_id ] +
                        [ 'fecha_registro' => $fecha_registro ] + 
                                    [ 'valor_pagado' => $nuevo_valor_pagado ] +  
                                    [ 'saldo_pendiente' => $nuevo_saldo_pendiente ] +
                                    [ 'estado' => $estado ] +
                        [ 'creado_por' => Auth()->user()->email ] +
                        [ 'modificado_por' => '' ] );
  }

  // Función para obtener los documentos de cartera con estado Pendiente
  public static function documentos_pendientes($codigo_referencia_tercero, $fecha_consulta)
  {
    /*
      ** La tabla cxc_movimientos guarda todos los documentos de cartera hechos desde cualquier aplicación
    */
    $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxc_movimientos.consecutivo) AS documento';
    $sql_movimiento_cxc = CxcEstadoCartera::leftJoin('cxc_movimientos','cxc_movimientos.id','=','cxc_estados_cartera.cxc_movimiento_id')
          ->leftJoin('core_terceros','core_terceros.id','=','cxc_movimientos.core_tercero_id')
          ->leftJoin('core_tipos_docs_apps','core_tipos_docs_apps.id','=','cxc_movimientos.core_tipo_doc_app_id')
          ->where('cxc_movimientos.codigo_referencia_tercero', $codigo_referencia_tercero)
          ->where('cxc_estados_cartera.fecha_registro', '<=', $fecha_consulta)
          ->where('cxc_estados_cartera.estado', 'Pendiente')
          ->orderBy('cxc_estados_cartera.fecha_registro','ASC')
          ->select(
                    'cxc_estados_cartera.fecha_registro',
                    'cxc_estados_cartera.valor_pagado',
                    'cxc_estados_cartera.saldo_pendiente',
                    'cxc_estados_cartera.estado',
                    'cxc_estados_cartera.cxc_movimiento_id',
                    'cxc_movimientos.valor_cartera',
                    'cxc_movimientos.core_tipo_doc_app_id',
                    'cxc_movimientos.consecutivo',
                    'core_terceros.descripcion AS tercero',
                    DB::raw($select_raw),
                    'cxc_movimientos.fecha',
                    'cxc_movimientos.fecha_vencimiento',
                    'cxc_movimientos.core_tipo_transaccion_id',
                    'cxc_movimientos.codigo_referencia_tercero',
                    'cxc_movimientos.detalle_operacion')
          ->unique();

    // Se crea un array y se concatenan los datos del documento de cartera según su origen
    $i = 0;
    foreach ($sql_movimiento_cxc as $fila) 
    {
      
      // Verificar si el documento no tiene un registro de estado de cartera donde ya esté pagado
      $cant = CxcEstadoCartera::where('cxc_movimiento_id',$fila->cxc_movimiento_id)->where('estado','Pagado')->count();

      $movimiento_cxc[$i] = $fila->toArray();

      // Para la fecha_consulta pueden haber documentos con estado Pendiente, pero que ya estén vencidos
      if ( $fecha_consulta > $fila->fecha_vencimiento ) 
      {
        $movimiento_cxc[$i]['estado'] = 'Vencido';
      }
      $i++;
    }
    return $movimiento_cxc;
  }

  // Función para obtener los documentos de cartera con estado Pagado
  public static function documentos_pagados($codigo_referencia_tercero, $fecha_consulta)
  {
    $sql_movimiento_cxc = CxcEstadoCartera::where('codigo_referencia_tercero', $codigo_referencia_tercero)
          ->where('fecha_registro', '<=', $fecha_consulta)
          ->where('estado', 'Pendiente')
          ->orderBy('fecha_registro','ASC')
          ->get()
          ->unique();

        // Para la fecha_consulta pueden haber documentos con estado <> a Pagado, pero que si están pagados en una fecha posterior, hay que retirar esos documentos de la consulta
        $i = 0; 
        foreach ($sql_movimiento_cxc as $fila) 
        {
          $registros_documento = CxcEstadoCartera::where('documento_cartera_id',$fila->id)->where('estado','Pagado')->get();

          if ( is_null($registros_documento) ) 
          {
            $movimiento_cxc[$i] = $fila->toArray();
            $i++;
          }else{
            $movimiento_cxc = [];
          }
        }

        return $movimiento_cxc;
  }



  // Se actualiza TEMPORALMENTE el estado de cartera para el registro de estado dado
  public static function actualizar_estado_cartera_en_la_consulta($registro_estado, $fecha_consulta)
  {
    // Respuesta: La tabla cxc_movimientos almacena los registros de documentos de cartera de todos los origenes (tipo_transaccion)

    $movimiento = CxcMovimiento::where('core_empresa_id', Auth::user()->empresa_id)->where('fecha_vencimiento','<=', $fecha_corte)
        ->get();

    foreach ($movimiento as $fila->core) 
    {
      $tipo_transaccion = TipoTransaccion::find($fila->transaccion_origen_doc_cartera_id);
      $doc_encabezado = app($tipo_transaccion->modelo_encabezados_documentos)->find($fila->documento_cartera_id);
      
    }


    CxcEstadoCartera::leftJoin('cxc_doc_encabezados','cxc_doc_encabezados.id','=','cxc_estados_cartera.documento_cartera_id')
        ->leftJoin('cxc_movimientos','cxc_movimientos.id','=','cxc_estados_cartera.documento_cartera_id')->where('cxc_doc_encabezados.fecha_vencimiento','<', $fecha_corte)
        ->where('cxc_estados_cartera.estado','<>', 'Pagado')
        ->where('cxc_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
        ->toBase()->update(['cxc_estados_cartera.estado' => 'Vencido','cxc_estados_cartera.updated_at' => date('Y-m-d h:m:s')]);

    //Para los que no están vencidos se les coloca pendiente
    CxcEstadoCartera::leftJoin('cxc_movimientos','cxc_movimientos.id','=','cxc_estados_cartera.documento_cartera_id')->where('cxc_movimientos.fecha_vencimiento','>', $fecha_corte)
        ->where('cxc_estados_cartera.estado','<>', 'Pagado')
        ->where('cxc_movimientos.core_empresa_id', Auth::user()->empresa_id)
        ->toBase()->update(['cxc_estados_cartera.estado' => 'Pendiente','cxc_estados_cartera.updated_at' => date('Y-m-d h:m:s')]);
  }

  public static function suma_saldos_pendientes($fecha, $codigo_referencia_tercero, $operador)
  {
    return CxcEstadoCartera::leftJoin('cxc_movimientos','cxc_movimientos.id','=','cxc_estados_cartera.documento_cartera_id')->where('cxc_estados_cartera.fecha_registro','<',$fecha)
        ->where('cxc_movimientos.codigo_referencia_tercero','=',$codigo_referencia_tercero)
        ->where('cxc_estados_cartera.estado','=','Vencido')
        ->where('cxc_estados_cartera.saldo_pendiente',$operador,0)
        ->distinct('cxc_estados_cartera.documento_cartera_id')
        ->sum('saldo_pendiente');
  }

  // Función para obtener los documentos de cartera
  public static function estados_de_cuentas($fecha_inicial, $fecha_final, $estado, $codigo_referencia_tercero, $operador, $core_tercero_id )
  {
      // fecha_final es Fecha de corte
      $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxc_movimientos.consecutivo) AS documento';

      $movimiento_cxc = CxcEstadoCartera::leftJoin('cxc_movimientos','cxc_movimientos.id','=','cxc_estados_cartera.documento_cartera_id')
            ->leftJoin('core_terceros','core_terceros.id','=','cxc_movimientos.core_tercero_id')
            ->leftJoin('core_tipos_docs_apps','core_tipos_docs_apps.id','=','cxc_movimientos.core_tipo_doc_app_id')
            ->where('cxc_estados_cartera.fecha_registro','<=',$fecha_final)
            ->where('cxc_movimientos.core_empresa_id',Auth::user()->empresa_id)
            ->where('cxc_estados_cartera.estado','LIKE',$estado)
            ->where('cxc_movimientos.codigo_referencia_tercero',$operador,$codigo_referencia_tercero)
            ->where('cxc_movimientos.core_tercero_id','LIKE',$core_tercero_id)
            ->orderBy('cxc_estados_cartera.fecha_registro','ASC')
            ->select(DB::raw($select_raw), 'core_terceros.descripcion AS tercero','core_terceros.telefono1 AS telefono','core_terceros.email AS email','cxc_movimientos.fecha','cxc_movimientos.fecha_vencimiento','cxc_movimientos.valor_cartera','cxc_estados_cartera.valor_pagado','cxc_estados_cartera.saldo_pendiente','cxc_movimientos.codigo_referencia_tercero','cxc_movimientos.id','cxc_estados_cartera.estado','cxc_movimientos.detalle_operacion')
            ->get()
            ->unique()
            ->toArray();

        return $movimiento_cxc;
  }
}