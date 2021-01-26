<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMovimiento;

class ReciboCaja extends Model
{
    // Apunta a la misma tabla del modelo de Recaudos
    protected $table = 'teso_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_empresa_id','core_tercero_id','codigo_referencia_tercero','teso_tipo_motivo','documento_soporte','descripcion','teso_medio_recaudo_id','teso_caja_id','teso_cuenta_bancaria_id','valor_total','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha','Documento','Tercero','Detalle','Valor total','Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"teso_recibo_caja_show/id_fila"}';
    
    public $archivo_js = 'assets/js/tesoreria/recibos_caja_egresos.js';
    
    public function tipo_transaccion()
    {
        return $this->belongsTo( 'App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id' );
    }
    
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
        return $this->belongsTo( TesoCaja::class, 'teso_caja_id');
    }

    public function cuenta_bancaria()
    {
        return $this->belongsTo( TesoCuentaBancaria::class, 'teso_cuenta_bancaria_id');
    }

    public function medio_recaudo()
    {
        return $this->belongsTo(TesoMedioRecaudo::class, 'teso_medio_recaudo_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $transaccion_id = 56;
        if ($search == "" )
        {
            return ReciboCaja::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.core_tipo_transaccion_id','=', $transaccion_id)
                    ->where('teso_doc_encabezados.core_empresa_id','=',Auth::user()->empresa_id)
                    ->select( 
                                'teso_doc_encabezados.fecha AS campo1',
                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo2'),
                                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                                'teso_doc_encabezados.descripcion AS campo4',
                                'teso_doc_encabezados.valor_total AS campo5',
                                'teso_doc_encabezados.estado AS campo6',
                                'teso_doc_encabezados.id AS campo7')
                    ->orderBy('teso_doc_encabezados.fecha', 'DESC')
                    ->paginate($nro_registros);
        }

    	return ReciboCaja::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.core_tipo_transaccion_id','=', $transaccion_id)
                    ->where('teso_doc_encabezados.core_empresa_id','=',Auth::user()->empresa_id)
                    ->select( 
                                'teso_doc_encabezados.fecha AS campo1',
                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo2'),
                                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                                'teso_doc_encabezados.descripcion AS campo4',
                                'teso_doc_encabezados.valor_total AS campo5',
                                'teso_doc_encabezados.estado AS campo6',
                                'teso_doc_encabezados.id AS campo7')
                    ->where("teso_doc_encabezados.fecha", "LIKE", "%$search%")
                    ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
                    ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
                    ->orWhere("teso_doc_encabezados.descripcion", "LIKE", "%$search%")
                    ->orWhere("teso_doc_encabezados.valor_total", "LIKE", "%$search%")
                    ->orWhere("teso_doc_encabezados.estado", "LIKE", "%$search%")/**/
                    ->orderBy('teso_doc_encabezados.fecha', 'DESC')
                    ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $transaccion_id = 56;
        $string = ReciboCaja::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.core_tipo_transaccion_id','=', $transaccion_id)
                    ->where('teso_doc_encabezados.core_empresa_id','=',Auth::user()->empresa_id)
                    ->select(
                                'teso_doc_encabezados.id AS ID',
                                'teso_doc_encabezados.fecha AS FECHA',
                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS DOCUMENTO'),
                                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS TERCERO'),
                                'teso_doc_encabezados.descripcion AS CONCEPTO',
                                'teso_doc_encabezados.valor_total AS VALOR',
                                'teso_doc_encabezados.estado AS ESTADO')
                    ->where("teso_doc_encabezados.fecha", "LIKE", "%$search%")
                    ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
                    ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
                    ->orWhere("teso_doc_encabezados.descripcion", "LIKE", "%$search%")
                    ->orWhere("teso_doc_encabezados.valor_total", "LIKE", "%$search%")
                    ->orWhere("teso_doc_encabezados.estado", "LIKE", "%$search%")/**/
                    ->orderBy('teso_doc_encabezados.fecha', 'DESC')
                    ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE RECIBOS DE CAJA";
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

    public static function get_campos_adicionales_create( $lista_campos )
    {
        $motivo_id = (int)config('tesoreria.motivo_recibo_caja_id');
        // Enviar formulario vacío. Se evita la creación, si se presiona el botón desde Académico Docente, pues no se han enviado ni el curos ni la asignatura 
        if ( $motivo_id == 0 ) 
        {
            return [
                        [
                                    "id" => 999,
                                    "descripcion" => "Label no se puede ingresar registros desde esta opción.",
                                    "tipo" => "personalizado",
                                    "name" => "lbl_planilla",
                                    "opciones" => "",
                                    "value" => '<div class="form-group">                    
                                                    <label class="control-label col-sm-3" style="color: red;" > <b> Debe asociar un Motivo de Tesorería para realizar esta transacción. </b> </label>
                                                    <br>
                                                    Haga click <a href="'.url( 'config?id=3&id_modelo=0' ).'"> Aquí </a> para configurarlo.
                                                </div>',
                                    "atributos" => [],
                                    "definicion" => "",
                                    "requerido" => 0,
                                    "editable" => 1,
                                    "unico" => 0
                                ]
                    ];          
        }

        return $lista_campos;
    }


    public static function store_adicional( $datos, $registro )
    {
        $teso_motivo_id = (int)config('tesoreria.motivo_recibo_caja_id');

        if ( explode("-", $datos['teso_medio_recaudo_id'])[1] == 'Efectivo' )
        {
            $datos['teso_cuenta_bancaria_id'] = 0;
        }else{
            $datos['teso_caja_id'] = 0;
        }

        $datos['consecutivo'] = $registro->consecutivo;

        $codigo_referencia_tercero = '';
        if ( $datos['vehiculo_id'] != '' )
        {
            $codigo_referencia_tercero = '{"ruta_modelo":"App\\\Contratotransporte\\\Vehiculo","registro_id":"'.$datos['vehiculo_id'].'"}';
        }

        TesoMovimiento::create( 
                                $datos +
                                [ 'teso_motivo_id' => $teso_motivo_id ] +
                                [ 'codigo_referencia_tercero' => $codigo_referencia_tercero ] +
                                [ 'valor_movimiento' => (float)$datos['valor_total'] ] +
                                [ 'estado' => 'Activo' ]
                            );
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            switch ($lista_campos[$i]['name'])
            {
                case 'teso_medio_recaudo_id':
                    $lista_campos[$i]['opciones'] = TesoMedioRecaudo::opciones_campo_select();
                    $opciones = TesoMedioRecaudo::opciones_campo_select();
                    $vec = [];
                    foreach ( $opciones as $key => $opcion )
                    {
                        $vec[] = [$key,$opcion];// (int)explode("-", $opcion)[0];
                        if ( (int)explode("-", $key)[0] == $registro->teso_medio_recaudo_id )
                        {
                            $lista_campos[$i]['value'] = $key;
                            //dd([(int)explode("-", $opcion)[0], $registro->teso_medio_recaudo_id]);
                        }
                    }                   
                    //dd($vec);
                    break;

                case 'teso_caja_id':
                    $lista_campos[$i]['opciones'] = TesoCaja::opciones_campo_select();
                    $lista_campos[$i]['value'] = $registro->teso_caja_id;
                    break;

                case 'teso_cuenta_bancaria_id':
                    $lista_campos[$i]['opciones'] = TesoCuentaBancaria::opciones_campo_select();
                    $lista_campos[$i]['value'] = $registro->teso_cuenta_bancaria_id;
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }

    public static function update_adicional( $datos, $registro_id )
    {
        $comprobante = ReciboCaja::find( $registro_id );

        $movimiento = TesoMovimiento::where( [
                                                [ 'core_tipo_transaccion_id', '=', $comprobante->core_tipo_transaccion_id ],
                                                [ 'core_tipo_doc_app_id', '=', $comprobante->core_tipo_doc_app_id ],
                                                [ 'consecutivo', '=', $comprobante->consecutivo ]
                                            ])
                                        ->get()
                                        ->first();

        $comprobante->core_tercero_id = $movimiento->core_tercero_id;
        $comprobante->save();

        $movimiento->update(
                            [ 
                                'documento_soporte' => $datos['documento_soporte'],
                                'descripcion' => $datos['descripcion'] 
                            ]
                        );
    }
}
