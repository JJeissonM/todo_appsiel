<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TesoMotivo extends Model
{

    /*
        movimiento: (ENUM) { entrada | salida }
        teso_tipo_motivo: (ENUM) { Recaudo cartera | Otros recaudos | Pago proveedores | Otros pagos | Anticipo | Anticipo proveedor | Traslado | Pago anticipado | Prestamo financiero }
    */
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'descripcion', 'movimiento', 'estado', 'teso_tipo_motivo', 'contab_cuenta_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Tipo de transacción', 'Movimiento', 'Cuenta contrapartida', 'Estado'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return TesoMotivo::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'teso_motivos.contab_cuenta_id')
            ->where('teso_motivos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'teso_motivos.descripcion AS campo1',
                'teso_motivos.teso_tipo_motivo AS campo2',
                'teso_motivos.movimiento AS campo3',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo4'),
                'teso_motivos.estado AS campo5',
                'teso_motivos.id AS campo6'
            )
            ->where("teso_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_motivos.teso_tipo_motivo", "LIKE", "%$search%")
            ->orWhere("teso_motivos.movimiento", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("teso_motivos.estado", "LIKE", "%$search%")
            ->orderBy('teso_motivos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = TesoMotivo::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'teso_motivos.contab_cuenta_id')
            ->where('teso_motivos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'teso_motivos.descripcion AS DESCRIPCIÓN',
                'teso_motivos.teso_tipo_motivo AS TIPO_DE_TRANSACCIÓN',
                'teso_motivos.movimiento AS MOVIMIENTO',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS CUENTA_CONTRAPARTIDA'),
                'teso_motivos.estado AS ESTADO'
            )
            ->where("teso_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_motivos.teso_tipo_motivo", "LIKE", "%$search%")
            ->orWhere("teso_motivos.movimiento", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere("teso_motivos.estado", "LIKE", "%$search%")
            ->orderBy('teso_motivos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOTIVOS TESORERIA";
    }

    public static function opciones_campo_select()
    {
        $opciones = TesoMotivo::where('teso_motivos.estado', 'Activo')
            ->select('teso_motivos.id', 'teso_motivos.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function opciones_campo_select_tipo_transaccion($teso_tipo_motivo)
    {
        $opciones = TesoMotivo::where('teso_motivos.estado', 'Activo')
            ->where('teso_tipo_motivo', $teso_tipo_motivo)
            ->select('teso_motivos.id', 'teso_motivos.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function cuenta_contable()
    {
        return $this->belongsTo('App\Contabilidad\ContabCuenta');
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"teso_doc_encabezados",
                                    "llave_foranea":"teso_tipo_motivo",
                                    "mensaje":"El Motivo ha sido utilizado en documentos de tesorería."
                                },
                            "1":{
                                    "tabla":"teso_doc_registros",
                                    "llave_foranea":"teso_motivo_id",
                                    "mensaje":"El Motivo ha sido utilizado en registros de documentos de tesorería."
                                },
                            "2":{
                                    "tabla":"teso_movimientos",
                                    "llave_foranea":"teso_motivo_id",
                                    "mensaje":"El Motivo tiene movimientos de tesorería."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
