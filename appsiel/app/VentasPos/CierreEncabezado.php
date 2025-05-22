<?php

namespace App\VentasPos;

use App\Ventas\VtasPedido;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class CierreEncabezado extends Model
{
    protected $table = 'vtas_pos_cierre_encabezados';
    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'cajero_id', 'pdv_id', 'detalle', 'creado_por', 'modificado_por', 'estado'];

    public $vistas = '{"show":"ventas_pos.index"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cajero', 'PDV', 'Detalle', 'Fecha creación', 'Estado'];
    public static function consultar_registros($nro_registros, $search)
    {
        return CierreEncabezado::leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_cierre_encabezados.pdv_id')
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_cierre_encabezados.core_tipo_doc_app_id')
            ->leftJoin('users', 'users.id', '=', 'vtas_pos_cierre_encabezados.cajero_id')
            ->select(
                'vtas_pos_cierre_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_cierre_encabezados.consecutivo) AS campo2'),
                'users.name AS campo3',
                'vtas_pos_puntos_de_ventas.descripcion AS campo4',
                'vtas_pos_cierre_encabezados.detalle AS campo5',
                'vtas_pos_cierre_encabezados.updated_at AS campo6',
                'vtas_pos_cierre_encabezados.estado AS campo7',
                'vtas_pos_cierre_encabezados.id AS campo8'
            )
            ->where("vtas_pos_cierre_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_cierre_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("vtas_pos_puntos_de_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_cierre_encabezados.detalle", "LIKE", "%$search%")
            ->orWhere("vtas_pos_cierre_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_cierre_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = CierreEncabezado::leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_cierre_encabezados.pdv_id')
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_cierre_encabezados.core_tipo_doc_app_id')
            ->leftJoin('users', 'users.id', '=', 'vtas_pos_cierre_encabezados.cajero_id')
            ->select(
                'vtas_pos_cierre_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_cierre_encabezados.consecutivo) AS DOCUMENTO'),
                'users.name AS CAJERO',
                'vtas_pos_puntos_de_ventas.descripcion AS PDV',
                'vtas_pos_cierre_encabezados.detalle AS DETALLE',
                'vtas_pos_cierre_encabezados.estado AS ESTADO'
            )
            ->where("vtas_pos_cierre_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_cierre_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("vtas_pos_puntos_de_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_cierre_encabezados.detalle", "LIKE", "%$search%")
            ->orWhere("vtas_pos_cierre_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_cierre_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CIERRES";
    }
    
    public static function opciones_campo_select()
    {
        $opciones = CierreEncabezado::where('vtas_pos_cierre_encabezados.estado', 'Activo')
            ->select('vtas_pos_cierre_encabezados.id', 'vtas_pos_cierre_encabezados.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public static function get_campos_adicionales_create($lista_campos)
    {
        $user = Auth::user();

        if ( $user->can('bloquear_cierre_si_hay_pedidos_pendientes') ) {

            $cantidad_pedidos_pendientes = VtasPedido::where('estado','Pendiente')->count();

            if($cantidad_pedidos_pendientes > 0)
            {
                return [
                    [
                        "id" => 999,
                        "descripcion" => "Label no se puede cerrar si hay pedidos pendientes.",
                        "tipo" => "personalizado",
                        "name" => "lbl_no_cerrar_si_pedidos_pendientes",
                        "opciones" => "",
                        "value" => '<div class="form-group">                    
                                                        <label class="control-label col-sm-3" style="color: red;" > <b> No puede hacer el CIERRE si tiene pedidos pendientes por facturar. </b> </label>      
                                                    </div>',
                        "atributos" => [],
                        "definicion" => "",
                        "requerido" => 0,
                        "editable" => 1,
                        "unico" => 0
                    ]
                ];
            }
        }

        $pdv = Pdv::find(Input::get('pdv_id'));

        $cajero = Cajero::find(Input::get('cajero_id'));

        // Agregar al comienzo del documento
        array_unshift($lista_campos, [
            "id" => 999,
            "descripcion" => "Punto de Venta",
            "tipo" => "personalizado",
            "name" => "lbl_pdv",
            "opciones" => "",
            "value" => '<div class="form-group">                    
                                                            <label class="control-label col-sm-3" > <b> Punto de venta: </b> </label>

                                                            <div class="col-sm-9">
                                                                ' . $pdv->descripcion . '
                                                                <input name="pdv_id" id="pdv_id" type="hidden" value="' . $pdv->id . '"/>
                                                            </div>                   
                                                        </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        array_unshift($lista_campos, [
            "id" => 999,
            "descripcion" => "Cajero",
            "tipo" => "personalizado",
            "name" => "lbl_cajero",
            "opciones" => "",
            "value" => '<div class="form-group">                    
                                                            <label class="control-label col-sm-3" > <b> Cajero: </b> </label>

                                                            <div class="col-sm-9">
                                                                ' . $cajero->name . '
                                                                <input name="cajero_id" id="cajero_id" type="hidden" value="' . $cajero->id . '"/>
                                                            </div>                   
                                                        </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        return $lista_campos;
    }

    public function store_adicional($datos, $registro)
    {
        $pdv = Pdv::find($datos['pdv_id']);
        $pdv->estado = 'Cerrado';
        $pdv->save();

        $registro->estado = 'Activo';
        $registro->save();
    }
}
