<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use App\Ventas\ClaseCliente;
use App\Ventas\Vendedor;
use App\Ventas\ContactoCliente;
use App\Ventas\DireccionEntrega;
use App\Core\Tercero;

use DB;
use Schema;

class Cliente extends Model
{
    protected $table = 'vtas_clientes';
	
	protected $fillable = ['core_tercero_id', 'encabezado_dcto_pp_id', 'clase_cliente_id', 'lista_precios_id', 'lista_descuentos_id', 'vendedor_id','inv_bodega_id', 'zona_id', 'liquida_impuestos', 'condicion_pago_id', 'cupo_credito', 'bloquea_por_cupo', 'bloquea_por_mora', 'estado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Identificación', 'Tercero', 'Dirección', 'Teléfono', 'Clase de cliente', 'Lista de precios', 'Lista de descuentos', 'Zona'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    public static function get_cuenta_cartera( $cliente_id )
    {
        $clase_cliente_id = Cliente::where( 'id', $cliente_id )->value( 'clase_cliente_id' );
        
        $cta_x_cobrar_id = ClaseCliente::where( 'id', $clase_cliente_id )->value( 'cta_x_cobrar_id' );

        if( is_null($cta_x_cobrar_id) )
        {
            return (int)config('configuracion.cta_cartera_default');
        }

        return $cta_x_cobrar_id;
    }

    public function lista_precios()
    {
        return $this->belongsTo(ListaPrecioEncabezado::class);
    }

    public function lista_descuentos()
    {
        return $this->belongsTo(ListaDctoEncabezado::class);
    }

    public function tercero()
    {
        return $this->belongsTo( Tercero::class, 'core_tercero_id');
    }

    public function clase_cliente()
    {
        return $this->belongsTo( ClaseCliente::class, 'clase_cliente_id');
    }

    public function condicion_pago()
    {
        return $this->belongsTo( CondicionPago::class, 'condicion_pago_id');
    }

    public function vendedor()
    {
        return $this->belongsTo( Vendedor::class, 'vendedor_id');
    }

    public function contactos()
    {
        return $this->hasMany(ContactoCliente::class, 'cliente_id');
    }

    public function direcciones_entrega()
    {
        return $this->hasMany( DireccionEntrega::class, 'cliente_id');
    }

	public static function consultar_registros($nro_registros, $search)
    {
        $array = Cliente::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_clientes.clase_cliente_id')
            ->leftJoin('vtas_listas_precios_encabezados', 'vtas_listas_precios_encabezados.id', '=', 'vtas_clientes.lista_precios_id')
            ->leftJoin('vtas_listas_dctos_encabezados', 'vtas_listas_dctos_encabezados.id', '=', 'vtas_clientes.lista_descuentos_id')
            ->leftJoin('vtas_zonas', 'vtas_zonas.id', '=', 'vtas_clientes.zona_id')
            ->select(
                'core_terceros.numero_identificacion AS campo1',
                'core_terceros.descripcion AS campo2',
                'core_terceros.direccion1 AS campo3',
                'core_terceros.telefono1 AS campo4',
                'vtas_clases_clientes.descripcion AS campo5',
                'vtas_listas_precios_encabezados.descripcion AS campo6',
                'vtas_listas_dctos_encabezados.descripcion AS campo7',
                'vtas_zonas.descripcion AS campo8',
                'vtas_clientes.id AS campo9'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere("vtas_clases_clientes.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_listas_precios_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_listas_dctos_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_zonas.descripcion", "LIKE", "%$search%")
            ->orderBy('vtas_clientes.created_at', 'DESC')
            ->paginate($nro_registros);

        if (count($array) > 0) {
            foreach ($array as $value) {
                //arreglamos la presentacion del telefono
                $value->campo4 = str_replace(' ', '', $value->campo4);
            }
        }

        return $array;
    }

    public static function sqlString($search)
    {
        $string = Cliente::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_clientes.clase_cliente_id')
            ->leftJoin('vtas_listas_precios_encabezados', 'vtas_listas_precios_encabezados.id', '=', 'vtas_clientes.lista_precios_id')
            ->leftJoin('vtas_listas_dctos_encabezados', 'vtas_listas_dctos_encabezados.id', '=', 'vtas_clientes.lista_descuentos_id')
            ->leftJoin('vtas_zonas', 'vtas_zonas.id', '=', 'vtas_clientes.zona_id')
            ->leftJoin('core_ciudades', 'core_ciudades.id', '=', 'core_terceros.codigo_ciudad')
            ->select(
                'core_terceros.numero_identificacion AS IDENTIFICACIÓN',
                'core_terceros.descripcion AS TERCERO',
                'core_terceros.direccion1 AS DIRECCIÓN',
                'core_ciudades.descripcion AS CIUDAD',
                'core_terceros.telefono1 AS TELÉFONO',
                'core_terceros.email AS EMAIL',
                'vtas_clases_clientes.descripcion AS CLASE_DE_CLIENTE',
                'vtas_listas_precios_encabezados.descripcion AS LISTA_DE_PRECIOS',
                'vtas_listas_dctos_encabezados.descripcion AS LISTA_DE_DESCUENTOS',
                'vtas_zonas.descripcion AS ZONA'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere("vtas_clases_clientes.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_listas_precios_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_listas_dctos_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_zonas.descripcion", "LIKE", "%$search%")
            ->orderBy('vtas_clientes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CLIENTES";
    }

    public static function opciones_campo_select()
    {
        $opciones = Cliente::leftJoin('core_terceros','core_terceros.id','=','vtas_clientes.core_tercero_id')->where('vtas_clientes.estado','Activo')
                    ->select('vtas_clientes.id','core_terceros.descripcion')
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"vtas_doc_encabezados",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene documentos de ventas estándar."
                                },
                            "1":{
                                    "tabla":"vtas_movimientos",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene movimientos de ventas estándar."
                                },
                            "2":{
                                    "tabla":"vtas_pos_doc_encabezados",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene documentos de ventas POS."
                                },
                            "3":{
                                    "tabla":"vtas_pos_movimientos",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente tiene movimientos de ventas POS."
                                },
                            "4":{
                                    "tabla":"vtas_pos_puntos_de_ventas",
                                    "llave_foranea":"cliente_default_id",
                                    "mensaje":"Cliente está asociado a punto de ventas (POS)."
                                },
                            "5":{
                                    "tabla":"vtas_vendedores",
                                    "llave_foranea":"cliente_id",
                                    "mensaje":"Cliente está asociado a un vendedor."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            if ( !Schema::hasTable( $una_tabla->tabla ) )
            {
                continue;
            }
            
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
