<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\ControlCheque;
use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\RegistroRetencion;
use App\Contabilidad\ContabMovimiento;

use App\Matriculas\FacturaAuxEstudiante;

use App\CxC\CxcMovimiento;

use App\CxP\CxpMovimiento;
use App\Compras\DescuentoProntoPago;
use App\Ventas\DescuentoPpEncabezado;

class TesoDocEncabezado extends Model
{
    // teso_tipo_motivo debe desaparecer, pues se una segun el motivo de cada registro del documento, en un mismom documento pueden haber varios teso_tipo_motivo
    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_empresa_id','core_tercero_id','codigo_referencia_tercero','teso_tipo_motivo','documento_soporte','descripcion','teso_medio_recaudo_id','teso_caja_id','teso_cuenta_bancaria_id','valor_total','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Fecha', 'Tercero', 'Detalle'];

    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function caja()
    {
        return $this->belongsTo(TesoCaja::class, 'teso_caja_id');
    }

    public function cuenta_bancaria()
    {
        return $this->belongsTo(TesoCuentaBancaria::class, 'teso_cuenta_bancaria_id');
    }

    public function medio_recaudo()
    {
        return $this->belongsTo(TesoMedioRecaudo::class, 'teso_medio_recaudo_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( TesoDocRegistro::class, 'teso_encabezado_id');
    }

    public function lineas_movimientos()
    {
        return TesoMovimiento::where( [ 
                                        'core_tipo_transaccion_id' => $this->core_tipo_transaccion_id,
                                        'core_tipo_doc_app_id' => $this->core_tipo_doc_app_id,
                                        'consecutivo' => $this->consecutivo
                                    ] )
                                ->get();
    } 

    public function actualizar_valor_total()
    {
        $this->valor_total = TesoDocRegistro::where('teso_encabezado_id',$this->id)->sum('valor');
        $this->save();
    }

    public function datos_auxiliares_estudiante()
    {
        $recaudo_libreta = TesoRecaudosLibreta::where('core_tipo_transaccion_id', $this->core_tipo_transaccion_id)
                                            ->where('core_tipo_doc_app_id', $this->core_tipo_doc_app_id)
                                            ->where('consecutivo', $this->consecutivo)
                                            ->get()
                                            ->first();
        if ( is_null($recaudo_libreta) )
        {
            return null;
        }

        return $recaudo_libreta->registro_cartera_estudiante->facturas_estudiantes[0];
    }

    public function cheques_relacionados_recaudos()
    {
        return ControlCheque::where('core_tipo_transaccion_id_origen', $this->core_tipo_transaccion_id)
                                        ->where('core_tipo_doc_app_id_origen', $this->core_tipo_doc_app_id)
                                        ->where('consecutivo', $this->consecutivo)
                                        ->get();
    }

    public function cheques_relacionados_pagos()
    {
        return ControlCheque::where('core_tipo_transaccion_id_consumo', $this->core_tipo_transaccion_id)
                                        ->where('core_tipo_doc_app_id_consumo', $this->core_tipo_doc_app_id)
                                        ->where('consecutivo_doc_consumo', $this->consecutivo)
                                        ->get();
    }

    public function retenciones_relacionadas()
    {
        return RegistroRetencion::where('core_tipo_transaccion_id', $this->core_tipo_transaccion_id)
                                        ->where('core_tipo_doc_app_id', $this->core_tipo_doc_app_id)
                                        ->where('consecutivo', $this->consecutivo)
                                        ->get(); 
    }

    public function get_resgitros_descuentos()
    {
        $cuentas_descuentos_ids = array_merge( DescuentoPpEncabezado::get()->pluck('contab_cuenta_id')->toArray(), DescuentoProntoPago::get()->pluck('contab_cuenta_id')->toArray() );
        return ContabMovimiento::where('core_tipo_transaccion_id', $this->core_tipo_transaccion_id)
                                        ->where('core_tipo_doc_app_id', $this->core_tipo_doc_app_id)
                                        ->where('consecutivo', $this->consecutivo)
                                        ->whereIn('contab_cuenta_id', $cuentas_descuentos_ids )
                                        ->get();
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo1'),
                'teso_doc_encabezados.fecha AS campo2',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'teso_doc_encabezados.descripcion AS campo4',
                'teso_doc_encabezados.id AS campo5'
            )
            ->where(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo1'), "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'), "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orderBy('teso_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo1'),
                'teso_doc_encabezados.fecha AS campo2',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'teso_doc_encabezados.descripcion AS campo4',
                'teso_doc_encabezados.id AS campo5'
            )
            ->where(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo1'), "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'), "LIKE", "%$search%")
            ->orWhere("teso_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orderBy('teso_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ";
    }


    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento';

        return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.id', $id)
                    ->select(DB::raw($select_raw),'teso_doc_encabezados.fecha','core_terceros.descripcion AS tercero','teso_doc_encabezados.descripcion AS detalle','teso_doc_encabezados.documento_soporte','teso_doc_encabezados.core_tipo_transaccion_id','teso_doc_encabezados.core_tipo_doc_app_id','teso_doc_encabezados.id','teso_doc_encabezados.creado_por','teso_doc_encabezados.consecutivo','teso_doc_encabezados.core_empresa_id','teso_doc_encabezados.core_tercero_id','teso_doc_encabezados.teso_tipo_motivo')
                    ->get()[0];
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {

        return TesoDocEncabezado::where('teso_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->select(
                                'teso_doc_encabezados.id',
                                'teso_doc_encabezados.core_empresa_id',
                                'teso_doc_encabezados.core_tercero_id',
                                'teso_doc_encabezados.core_tipo_transaccion_id',
                                'teso_doc_encabezados.core_tipo_doc_app_id',
                                'teso_doc_encabezados.consecutivo',
                                'teso_doc_encabezados.teso_caja_id',
                                'teso_doc_encabezados.fecha',
                                'teso_doc_encabezados.descripcion',
                                'teso_doc_encabezados.teso_medio_recaudo_id',
                                'teso_doc_encabezados.teso_caja_id',
                                'teso_doc_encabezados.teso_cuenta_bancaria_id',
                                'teso_doc_encabezados.valor_total',
                                'teso_doc_encabezados.estado',
                                'teso_doc_encabezados.creado_por',
                                'teso_doc_encabezados.created_at',
                                'teso_doc_encabezados.modificado_por',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero_nombre_completo' ),
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get()
                    ->first();
    }

    public function transacciones_adicionales( $datos, $tipo_operacion, $valor )
    {

        // Solo los anticipos de clientes se guardan en el movimiento de cartera (CxC)
        if ( $tipo_operacion == 'Anticipo' )
        {
            $datos['valor_documento'] = $valor * -1;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $valor * -1;
            $datos['fecha_vencimiento'] = $datos['fecha'];
            $datos['estado'] = 'Pendiente';
            CxcMovimiento::create( $datos );
        }
 
        // Generar CxP porque se utilizó dinero de un agente externo (banco, coopertaiva, tarjeta de crédito).
        if ( $tipo_operacion == 'Prestamo financiero' )
        {
            $datos['valor_documento'] = $valor;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $valor;
            $datos['fecha_vencimiento'] = $datos['fecha'];
            $datos['estado'] = 'Pendiente';
            CxpMovimiento::create( $datos );
        }

        // Generar CxP a favor. Saldo negativo por pagar (a favor de la empresa)
        if ( $tipo_operacion == 'Anticipo proveedor' )
        {
            $datos['valor_documento'] = $valor * -1;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $valor * -1;
            $datos['fecha_vencimiento'] = $datos['fecha'];
            $datos['estado'] = 'Pendiente';
            CxpMovimiento::create( $datos );
        }

        // Generar CxC por algún dinero prestado o anticipado a trabajadores o clientes.
        if ( $tipo_operacion == 'Pago anticipado' )
        {
            $datos['valor_documento'] = $valor;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $valor;
            $datos['fecha_vencimiento'] = $datos['fecha'];
            $datos['estado'] = 'Pendiente';
            CxcMovimiento::create( $datos );
        }
    }
}
