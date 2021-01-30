<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Core\Acl;

use App\Tesoreria\TesoEntidadFinanciera;

class TesoCuentaBancaria extends Model
{
    protected $table = 'teso_cuentas_bancarias';

    protected $fillable = ['core_empresa_id','entidad_financiera_id','tipo_cuenta','descripcion','por_defecto','estado','contab_cuenta_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Entidad financiera', 'Tipo cuenta', 'Número', 'Cta. contable', 'Por defecto', 'Estado'];

    public function entidad_financiera()
    {
        return $this->belongsTo(TesoEntidadFinanciera::class, 'entidad_financiera_id');
    }

    /*public $vistas = [ 
                        'create' => 'web',
                        'edit' => ''
                        ];*/

    public static function consultar_registros($nro_registros, $search)
    {
        return TesoCuentaBancaria::leftJoin('teso_entidades_financieras', 'teso_entidades_financieras.id', '=', 'teso_cuentas_bancarias.entidad_financiera_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'teso_cuentas_bancarias.contab_cuenta_id')
            ->select(
                'teso_entidades_financieras.descripcion AS campo1',
                'teso_cuentas_bancarias.tipo_cuenta AS campo2',
                'teso_cuentas_bancarias.descripcion AS campo3',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo4'),
                'teso_cuentas_bancarias.por_defecto AS campo5',
                'teso_cuentas_bancarias.estado AS campo6',
                'teso_cuentas_bancarias.id AS campo7'
            )
            ->where("teso_entidades_financieras.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.tipo_cuenta", "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.por_defecto", "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.estado", "LIKE", "%$search%")
            ->orderBy('teso_cuentas_bancarias.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = TesoCuentaBancaria::leftJoin('teso_entidades_financieras', 'teso_entidades_financieras.id', '=', 'teso_cuentas_bancarias.entidad_financiera_id')
            ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'teso_cuentas_bancarias.contab_cuenta_id')
            ->select(
                'teso_entidades_financieras.descripcion AS ENTIDAD_FINANCIERA',
                'teso_cuentas_bancarias.tipo_cuenta AS TIPO_CUENTA',
                'teso_cuentas_bancarias.descripcion AS NÚMERO',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS CTA._CONTABLE'),
                'teso_cuentas_bancarias.por_defecto AS POR_DEFECTO',
                'teso_cuentas_bancarias.estado AS ESTADO'
            )
            ->where("teso_entidades_financieras.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.tipo_cuenta", "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.por_defecto", "LIKE", "%$search%")
            ->orWhere("teso_cuentas_bancarias.estado", "LIKE", "%$search%")
            ->orderBy('teso_cuentas_bancarias.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CUENTAS BANCARIAS";
    }

    public static function opciones_campo_select()
    {
        $opciones = self::get_cuentas_permitidas();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->entidad_financiera . ' - ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_cuentas_permitidas()
    {
        $cuentas = [];
        $user = Auth::user();
        if( $user->hasRole('Agencia') )
        {
            $acl = Acl::where([
                            ['modelo_recurso_id','=',33],
                            ['user_id','=',Auth::user()->id] ,
                            ['permiso_concedido','=',1] 
                        ] )
                    ->get()->first();

            if (!is_null($acl))
            {
                $cuentas = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('teso_cuentas_bancarias.id',$acl->recurso_id)
                            ->where('teso_cuentas_bancarias.estado','Activo')
                            ->select('teso_cuentas_bancarias.id','teso_cuentas_bancarias.descripcion','teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get();
            }
            
        }else{
            $cuentas = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('teso_cuentas_bancarias.estado','Activo')
                            ->select('teso_cuentas_bancarias.id','teso_cuentas_bancarias.descripcion','teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get();
        }

        return $cuentas;
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
