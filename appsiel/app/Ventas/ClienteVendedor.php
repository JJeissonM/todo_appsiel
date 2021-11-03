<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\Core\Tercero;

use App\Ventas\Cliente;
use App\Ventas\ClaseCliente;
use App\Ventas\Vendedor;

// Solo para usuarios con el role Vendedor
class ClienteVendedor extends Cliente
{
    protected $table = 'vtas_clientes';
	
	protected $fillable = [ 'core_tercero_id', 'encabezado_dcto_pp_id', 'clase_cliente_id', 'lista_precios_id', 'lista_descuentos_id', 'vendedor_id','inv_bodega_id', 'zona_id', 'liquida_impuestos', 'condicion_pago_id', 'cupo_credito', 'bloquea_por_cupo', 'bloquea_por_mora', 'estado' ];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Identificación', 'Tercero', 'Dirección', 'Teléfono', 'Lista de precios', 'Lista de descuentos', 'Zona'];

    public $urls_acciones = '{"index":"web","create":"web/create","edit":"web/id_fila/edit","store":"web","update":"web/id_fila","show":"vtas_clientes/id_fila","eliminar":"no"}';

	public static function consultar_registros($nro_registros, $search)
    {

        $array_wheres = [['vtas_clientes.id', '>', 0]];

        $vendedor = Vendedor::where('user_id', Auth::user()->id)->get()->first();

        if (!is_null($vendedor))
        {
            $array_wheres = array_merge($array_wheres, [['vtas_clientes.vendedor_id','=',$vendedor->id]]);
        }

        return Cliente::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_clientes.clase_cliente_id')
            ->leftJoin('vtas_listas_precios_encabezados', 'vtas_listas_precios_encabezados.id', '=', 'vtas_clientes.lista_precios_id')
            ->leftJoin('vtas_listas_dctos_encabezados', 'vtas_listas_dctos_encabezados.id', '=', 'vtas_clientes.lista_descuentos_id')
            ->leftJoin('vtas_zonas', 'vtas_zonas.id', '=', 'vtas_clientes.zona_id')
            ->where($array_wheres)
            ->select('core_terceros.numero_identificacion AS campo1', 'core_terceros.descripcion AS campo2', 'core_terceros.direccion1 AS campo3', 'core_terceros.telefono1 AS campo4', 'vtas_listas_precios_encabezados.descripcion AS campo5', 'vtas_listas_dctos_encabezados.descripcion AS campo6', 'vtas_zonas.descripcion AS campo7', 'vtas_clientes.id AS campo8')
            ->orderBy('vtas_clientes.created_at', 'DESC')
            ->paginate($nro_registros);
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
    

    public static function get_cuenta_cartera( $cliente_id )
    {
        $clase_cliente_id = Cliente::where( 'id', $cliente_id )->value( 'clase_cliente_id' );
        return ClaseCliente::where( 'id', $clase_cliente_id )->value( 'cta_x_cobrar_id' );
    }

    public function store_adicional( $datos, $registro )
    {
        $estado_cliente = $datos['estado'];
        // Se copia los datos asociados al Usuario/Vendedor que esta creando al cliente
        $user_id = Auth::user()->id;
        $vendedor = Vendedor::where('user_id',$user_id)->get()->first();
        $datos_tercero = $vendedor->tercero;
        $datos_cliente = $vendedor->cliente;

        $datos['codigo_ciudad'] = $datos_tercero->codigo_ciudad;
        $datos['core_empresa_id'] = $datos_tercero->core_empresa_id;
        $datos['tipo'] = $datos_tercero->tipo;
        $datos['id_tipo_documento_id'] = $datos_tercero->id_tipo_documento_id;
        $datos['digito_verificacion'] = $datos_tercero->digito_verificacion;

        $datos['encabezado_dcto_pp_id'] = $datos_cliente->encabezado_dcto_pp_id;
        $datos['clase_cliente_id'] = $datos_cliente->clase_cliente_id;
        $datos['lista_precios_id'] = $datos_cliente->lista_precios_id;
        $datos['lista_descuentos_id'] = $datos_cliente->lista_descuentos_id;
        $datos['vendedor_id'] = $vendedor->id;
        $datos['inv_bodega_id'] = $datos_cliente->inv_bodega_id;
        $datos['zona_id'] = $datos_cliente->zona_id;
        $datos['liquida_impuestos'] = $datos_cliente->liquida_impuestos;
        $datos['condicion_pago_id'] = $datos_cliente->condicion_pago_id;
        $datos['cupo_credito'] = $datos_cliente->cupo_credito;
        $datos['bloquea_por_cupo'] = $datos_cliente->bloquea_por_cupo;
        $datos['bloquea_por_mora'] = $datos_cliente->bloquea_por_mora;
        
        $datos['estado'] = 'Activo';
        $datos['creado_por'] = Auth::user()->email;

        $tercero = new Tercero;    
        $datos['razon_social'] = $datos['descripcion'];    
        $tercero->fill( $datos );
        $tercero->save();

        $datos['core_tercero_id'] = $tercero->id;
        
        // Datos del Cliente
        $registro->fill( $datos );
        $datos['estado'] = $estado_cliente;
        $registro->save();

        // Crear contacto de asociado al cliente
        if ( $datos['nombre_contacto'] != '' )
        {
            // Crear tercero
            $datos['descripcion'] = $datos['nombre_contacto'];
            $datos['telefono1'] = $datos['telefono1'];
            $datos['email'] = $datos['email'];
            $datos['numero_identificacion'] = $datos['telefono1'];
            $tercero2 = new Tercero;
            $tercero2->fill( $datos );
            $tercero2->save();

            // Asociar nuevo tercero al cliente
            $contacto = new ContactoCliente();
            $contacto->core_tercero_id = $tercero2->id;
            $contacto->cliente_id = $registro->id;
            $contacto->estado = 'Activo';
            $contacto->save();
        }
    }    

    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $contacto_default = $registro->contactos->first();

        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ($lista_campos[$i]['name'])
            {
                case 'nombre_contacto':
                    if( !is_null($contacto_default) )
                    {
                        $lista_campos[$i]['value'] = $contacto_default->tercero->descripcion;
                    }
                    break;

                
                case 'contacto_id':
                    if( !is_null($contacto_default) )
                    {
                        $lista_campos[$i]['value'] = $contacto_default->id;
                    }
                    break;

                default:
                    # code...
                    break;
            }
        }

        $tercero = Tercero::find($registro->core_tercero_id);
        $registro->descripcion = $tercero->descripcion;
        $registro->nombre1 = $tercero->nombre1;
        $registro->otros_nombres = $tercero->otros_nombres;
        $registro->apellido1 = $tercero->apellido1;
        $registro->apellido2 = $tercero->apellido2;
        $registro->id_tipo_documento_id = $tercero->id_tipo_documento_id;
        $registro->numero_identificacion = $tercero->numero_identificacion;
        $registro->digito_verificacion = $tercero->digito_verificacion;
        $registro->direccion1 = $tercero->direccion1;
        $registro->direccion2 = $tercero->direccion2; // Se está usando para fecha de cumpleaños
        $registro->telefono1 = $tercero->telefono1;
        $registro->codigo_ciudad = $tercero->codigo_ciudad;
        $registro->tipo = $tercero->tipo;
        $registro->razon_social = $tercero->razon_social;
        $registro->email = $tercero->email;

        return $lista_campos;
    }


    public function update_adicional($datos, $id)
    {
        $registro = Cliente::find( $id );
        $tercero_cliente = $registro->tercero;
        
        $tercero_cliente->descripcion = $datos['descripcion'];
        $tercero_cliente->numero_identificacion = $datos['numero_identificacion'];
        $tercero_cliente->direccion1 = $datos['direccion1'];
        $tercero_cliente->telefono1 = $datos['telefono1'];
        $tercero_cliente->email = $datos['email'];
        $tercero_cliente->save();

        $contacto_default = ContactoCliente::find( (int)$datos['contacto_id'] );
        //dd( $contacto_default );
        if( !is_null($contacto_default) )
        {
            $tercero_contacto = $contacto_default->tercero;

            $tercero_contacto->descripcion = $datos['nombre_contacto'];
            $tercero_contacto->direccion1 = $datos['direccion1'];
            $tercero_contacto->telefono1 = $datos['telefono1'];
            $tercero_contacto->email = $datos['email'];
            $tercero_contacto->save();
        }
        

    }
}
