<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;
use Schema;

use App\Core\Tercero;

class ContactoCliente extends Model
{
    protected $table = 'vtas_contactos_clientes';
	
	protected $fillable = [ 'core_tercero_id', 'cliente_id', 'estado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Cliente', 'Contacto',  'Email Contacto',  'Tel. Contacto', 'Estado'];

    public $urls_acciones = '{ "create":"web/create", "edit":"web/id_fila/edit", "eliminar":"web_eliminar/id_fila"}';

	public static function consultar_registros($nro_registros, $search)
    {
        $array = ContactoCliente::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_contactos_clientes.core_tercero_id')
                                    ->leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'vtas_contactos_clientes.cliente_id')
                                    ->leftJoin('core_terceros as tercero_cliente', 'tercero_cliente.id', '=', 'vtas_clientes.core_tercero_id')
                                    ->select(
						                'tercero_cliente.descripcion AS campo1',
						                'core_terceros.descripcion AS campo2',
						                'core_terceros.email AS campo3',
						                'core_terceros.telefono1 AS campo4',
						                'vtas_contactos_clientes.estado AS campo5',
						                'vtas_contactos_clientes.id AS campo6'
						            )
						            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
						            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
						            ->orWhere("vtas_contactos_clientes.cliente_id", "LIKE", "%$search%")
						            ->orderBy('vtas_contactos_clientes.created_at', 'DESC')
						            ->paginate($nro_registros);

        return $array;
    }

    public static function sqlString($search)
    {
        $string = Cliente::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_contactos_clientes.core_tercero_id')
						            ->select(
						                'core_terceros.descripcion AS campo1',
						                'vtas_contactos_clientes.cliente_id AS campo2',
						                'vtas_contactos_clientes.id AS campo3'
						            )
					            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
					            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
					            ->orderBy('vtas_contactos_clientes.created_at', 'DESC')
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
        $opciones = ContactoCliente::leftJoin('core_terceros','core_terceros.id','=','vtas_contactos_clientes.core_tercero_id')->where('vtas_contactos_clientes.estado','Activo')
                    ->select('vtas_contactos_clientes.id','core_terceros.descripcion')
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional( $datos, $registro )
    {
    	$datos['tipo'] = 'Persona natural';
    	$datos['core_empresa_id'] = Auth::user()->empresa;
    	$datos['numero_identificacion'] = rand(1111,99999);
    	$datos['codigo_ciudad'] = 16920001;
    	$datos['creado_por'] = Auth::user()->email;

    	$tercero = Tercero::create( $datos );

    	$registro->core_tercero_id = $tercero->id;
    	$registro->save();
    }

    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $tercero = Tercero::find( $registro->core_tercero_id );

        /*
            Personalizar los campos
        */
        $cantidad_campos = count($lista_campos);
        for ($i = 0; $i <  $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name'])
            {
                case 'descripcion':
                    $lista_campos[$i]['value'] = $tercero->descripcion;
                    break;
                case 'email':
                    $lista_campos[$i]['value'] = $tercero->email;
                    break;
                case 'telefono1':
                    $lista_campos[$i]['value'] = $tercero->telefono1;
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
    	$contacto = ContactoCliente::find( $registro_id );
        $tercero = Tercero::find( $contacto->core_tercero_id );

        $tercero->descripcion = $datos['descripcion'];
        $tercero->email = $datos['email'];
        $tercero->telefono1 = $datos['telefono1'];
        $tercero->save();

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
