<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

use App\Inventarios\InvBodega;
use App\Tesoreria\TesoCaja;
use App\Ventas\Cliente;
use App\Core\TipoDocApp;

class Pdv extends Model
{
    protected $table = 'vtas_pos_puntos_de_ventas';		
	protected $fillable = ['core_empresa_id', 'descripcion', 'bodega_default_id', 'caja_default_id', 'cajero_default_id', 'cliente_default_id', 'tipo_doc_app_default_id', 'detalle', 'direccion', 'telefono', 'email', 'creado_por', 'modificado_por', 'estado'];

    public function bodega()
    {
        return $this->belongsTo( InvBodega::class,'bodega_default_id');
    }

    public function caja()
    {
        return $this->belongsTo( TesoCaja::class,'caja_default_id');
    }

    public function cajero()
    {
        return $this->belongsTo( \App\User::class,'cajero_default_id');
    }

    public function cliente()
    {
        return $this->belongsTo( Cliente::class,'cliente_default_id');
    }

    public function tipo_doc_app()
    {
        return $this->belongsTo( TipoDocApp::class,'tipo_doc_app_default_id');
    }

    public function ultima_fecha_apertura()
    {
        $ultima_apertura = AperturaEncabezado::where('pdv_id',$this->id)->orderBy('created_at', 'desc')->get()->first();

        if ( $ultima_apertura == null ) {
            return date('Y-m-d');
        }
        return $ultima_apertura->fecha;
    }

    public function get_valor_base_ultima_apertura()
    {
        $ultima_apertura = AperturaEncabezado::where('pdv_id',$this->id)->orderBy('created_at', 'desc')->get()->first();

        if ( $ultima_apertura == null ) {
            return 0;
        }
        return $ultima_apertura->efectivo_base;
    }


    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';
	
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Bodega', 'Caja', 'Cajero', 'Cliente', 'Tipo Doc.', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        return Pdv::leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'vtas_pos_puntos_de_ventas.bodega_default_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'vtas_pos_puntos_de_ventas.caja_default_id')
            ->leftJoin('users', 'users.id', '=', 'vtas_pos_puntos_de_ventas.cajero_default_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'vtas_pos_puntos_de_ventas.cliente_default_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_puntos_de_ventas.tipo_doc_app_default_id')
            ->select(
                'vtas_pos_puntos_de_ventas.descripcion AS campo1',
                'inv_bodegas.descripcion AS campo2',
                'teso_cajas.descripcion AS campo3',
                'users.name AS campo4',
                'core_terceros.descripcion AS campo5',
                'core_tipos_docs_apps.descripcion AS campo6',
                'vtas_pos_puntos_de_ventas.estado AS campo7',
                'vtas_pos_puntos_de_ventas.id AS campo8'
            )
            ->where("vtas_pos_puntos_de_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_cajas.descripcion", "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_puntos_de_ventas.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_puntos_de_ventas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Pdv::leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'vtas_pos_puntos_de_ventas.bodega_default_id')
            ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'vtas_pos_puntos_de_ventas.caja_default_id')
            ->leftJoin('users', 'users.id', '=', 'vtas_pos_puntos_de_ventas.cajero_default_id')
            ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'vtas_pos_puntos_de_ventas.cliente_default_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_puntos_de_ventas.tipo_doc_app_default_id')
            ->select(
                'vtas_pos_puntos_de_ventas.descripcion AS DESCRIPCIÓN',
                'inv_bodegas.descripcion AS BODEGA',
                'teso_cajas.descripcion AS CAJA',
                'users.name AS CAJERO',
                'core_terceros.descripcion AS CLIENTE',
                'core_tipos_docs_apps.descripcion AS TIPO_DOC.',
                'vtas_pos_puntos_de_ventas.estado AS ESTADO'
            )
            ->where("vtas_pos_puntos_de_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_cajas.descripcion", "LIKE", "%$search%")
            ->orWhere("users.name", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_puntos_de_ventas.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_puntos_de_ventas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PUNTOS DE VENTA";
    }

	public static function opciones_campo_select()
    {
        $opciones = Pdv::where('vtas_pos_puntos_de_ventas.estado','Activo')
                    ->select('vtas_pos_puntos_de_ventas.id','vtas_pos_puntos_de_ventas.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
