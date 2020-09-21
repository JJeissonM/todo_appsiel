<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Tesoreria\TesoEntidadFinanciera;

class TesoCuentaBancaria extends Model
{
    protected $table = 'teso_cuentas_bancarias';

    protected $fillable = ['core_empresa_id','entidad_financiera_id','tipo_cuenta','descripcion','por_defecto','estado','contab_cuenta_id'];

    public $encabezado_tabla = ['ID','Entidad financiera','Tipo cuenta','Número','Cta. contable','Por defecto','Estado','Acción'];

    public function entidad_financiera()
    {
        return $this->belongsTo( TesoEntidadFinanciera::class, 'entidad_financiera_id');
    }

    /*public $vistas = [ 
                        'create' => 'web',
                        'edit' => ''
                        ];*/

    public static function consultar_registros()
    {
        return TesoCuentaBancaria::leftJoin('teso_entidades_financieras', 'teso_entidades_financieras.id', '=', 'teso_cuentas_bancarias.entidad_financiera_id')
                    ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'teso_cuentas_bancarias.contab_cuenta_id')
                    ->select(
                                'teso_cuentas_bancarias.id AS campo1',
                                'teso_entidades_financieras.descripcion AS campo2',
                                'teso_cuentas_bancarias.tipo_cuenta AS campo3',
                                'teso_cuentas_bancarias.descripcion AS campo4',
                                DB::raw( 'CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo5' ),
                                'teso_cuentas_bancarias.por_defecto AS campo6',
                                'teso_cuentas_bancarias.estado AS campo7',
                                'teso_cuentas_bancarias.id AS campo8'
                            )
                    ->get()
                    ->toArray();
    }

    public static function opciones_campo_select()
    {
        $opciones = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('teso_cuentas_bancarias.estado','Activo')
                            ->select('teso_cuentas_bancarias.id','teso_cuentas_bancarias.descripcion','teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->entidad_financiera . ' - ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_cuenta_por_defecto()
    {
        $cuenta_por_defecto = ['tipo_cuenta'=>'Sin Cta. por defecto','descripcion'=>'Sin Cta. por defecto','entidad_financiera'=>'Sin Cta. por defecto'];

        $registro = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('teso_cuentas_bancarias.core_empresa_id',Auth::user()->empresa_id)
                            ->where('teso_cuentas_bancarias.por_defecto','Si')
                            ->select('teso_cuentas_bancarias.tipo_cuenta','teso_cuentas_bancarias.descripcion','teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get()
                            ->toArray();

        if ( empty( $registro) ) 
        {
            return $cuenta_por_defecto;
        }

        return $registro[0];
    }

    public static function get_datos_basicos( $id )
    {
        return TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('teso_cuentas_bancarias.id', $id)
                            ->select(
                                        'teso_cuentas_bancarias.tipo_cuenta',
                                        'teso_cuentas_bancarias.descripcion',
                                        'teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get()
                            ->first();
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"contab_movimientos",
                                    "llave_foranea":"teso_cuenta_bancaria_id",
                                    "mensaje":"Está en movimientos contables."
                                },
                            "1":{
                                    "tabla":"teso_doc_encabezados",
                                    "llave_foranea":"teso_cuenta_bancaria_id",
                                    "mensaje":"Está en documentos de tesorería."
                                },
                            "2":{
                                    "tabla":"teso_doc_registros",
                                    "llave_foranea":"teso_cuenta_bancaria_id",
                                    "mensaje":"Está en registros de documentos de tesorería."
                                },
                            "3":{
                                    "tabla":"teso_movimientos",
                                    "llave_foranea":"teso_cuenta_bancaria_id",
                                    "mensaje":"Está en movimientos de tesorería."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
