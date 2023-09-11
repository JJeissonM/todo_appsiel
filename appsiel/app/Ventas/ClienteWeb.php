<?php

namespace App\Ventas;

use App\Core\Tercero;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClienteWeb extends Model
{
    protected $table = 'vtas_clientes';
	
	protected $fillable = ['core_tercero_id', 'encabezado_dcto_pp_id', 'clase_cliente_id', 'lista_precios_id', 'lista_descuentos_id', 'vendedor_id','inv_bodega_id', 'zona_id', 'liquida_impuestos', 'condicion_pago_id', 'cupo_credito', 'bloquea_por_cupo', 'bloquea_por_mora', 'estado'];

    public $urls_acciones = '{"create":"web/create","show":"ecommerce/public/newaccount"}';

    public $archivo_js = 'assets/tienda/js/cliente_web.js';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Identificación', 'Tercero', 'Dirección', 'Clase de cliente', 'Lista de precios', 'Lista de descuentos', 'Zona'];

    public function direcciones_entrega()
    {
        return $this->hasMany( DireccionEntrega::class, 'cliente_id');
    }

    public function direccion_por_defecto(){
        return DireccionEntrega::where('por_defecto',1)->get()->first();
    }

    public static function consultar_registros($nro_registros)
    {
        $registros = ClienteWeb::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_clientes.clase_cliente_id')->leftJoin('vtas_listas_precios_encabezados', 'vtas_listas_precios_encabezados.id', '=', 'vtas_clientes.lista_precios_id')->leftJoin('vtas_listas_dctos_encabezados', 'vtas_listas_dctos_encabezados.id', '=', 'vtas_clientes.lista_descuentos_id')->leftJoin('vtas_zonas', 'vtas_zonas.id', '=', 'vtas_clientes.zona_id')
            ->select('core_terceros.numero_identificacion AS campo1', 'core_terceros.descripcion AS campo2', 'core_terceros.direccion1 AS campo3', 'vtas_clases_clientes.descripcion AS campo4', 'vtas_listas_precios_encabezados.descripcion AS campo5', 'vtas_listas_dctos_encabezados.descripcion AS campo6', 'vtas_zonas.descripcion AS campo7', 'vtas_clientes.id AS campo8')
            ->orderBy('vtas_clientes.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = ClienteWeb::leftJoin('core_terceros','core_terceros.id','=','vtas_clientes.core_tercero_id')->where('vtas_clientes.estado','Activo')
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

    public static function get_datos_basicos( $cliente_id,$query )
    {
        return ClienteWeb::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
                            ->leftJoin('users', 'users.id', '=', 'core_terceros.user_id')
                            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
                            ->leftJoin('core_ciudades', 'core_ciudades.id', '=', 'core_terceros.codigo_ciudad')
                            ->leftJoin('core_departamentos', 'core_departamentos.id', '=', 'core_ciudades.core_departamento_id')
                            ->leftJoin('core_paises', 'core_paises.id', '=', 'core_departamentos.codigo_pais')
                            ->where($query,$cliente_id)
                            ->select( 
                                        DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS nombre_completo' ),
                                        DB::raw( 'CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS tipo_y_numero_documento_identidad' ),
                                        'core_terceros.user_id',
                                        'core_terceros.imagen',
                                        'core_terceros.nombre1',
                                        'core_terceros.otros_nombres',
                                        'core_terceros.apellido1',
                                        'core_terceros.apellido2',
                                        'core_terceros.telefono1',
                                        'core_terceros.id_tipo_documento_id',
                                        'core_terceros.numero_identificacion',
                                        'core_terceros.direccion1',
                                        'core_terceros.direccion2',
                                        'core_terceros.barrio',
                                        'core_terceros.codigo_postal',
                                        'core_ciudades.descripcion AS ciudad',
                                        'core_departamentos.descripcion AS departamento',
                                        'core_paises.descripcion AS pais',
                                        'users.email',
                                        'vtas_clientes.id')
                            ->get()
                            ->first();
    }

    // Solo se creó un registro vacío en la tabla clientes
    public function store_adicional($datos, $registro)
    {
        // Separa los nombres
        $nombre1 = $datos['nombre1'];

        // Separa los apellidos
        $apellido1 = $datos['apellido1'];

        $array_tercero = [ 
                            'core_empresa_id' => 1,
                            'tipo' => 'Persona natural',
                            'nombre1' => $nombre1,
                            'otros_nombres' => '',
                            'apellido1' => $apellido1,
                            'apellido2' => '',
                            'descripcion' => $nombre1 .  " " . $apellido1,
                            'id_tipo_documento_id' => config('configuracion.tipo_doc_identidad_default'),
                            'numero_identificacion' => $datos['numero_identificacion'],
                            'digito_verificacion' => 0,
                            'direccion1' => $datos['direccion1'],
                            'codigo_ciudad' => 16920001,
                            'telefono1' => $datos['telefono1'],
                            'email' => $datos['email'],
                            'estado' => 'Activo' ];

        $tercero = Tercero::create( $array_tercero );

        // Actualizar Cliente
        $registro->core_tercero_id = $tercero->id;

        $registro->id_tipo_documento_id = config('configuracion.tipo_doc_identidad_default');
        $registro->clase_cliente_id = config('pagina_web.clase_cliente_id');
        $registro->lista_precios_id = config('pagina_web.lista_precios_id');
        $registro->lista_descuentos_id = config('pagina_web.lista_descuentos_id');
        $registro->zona_id = config('pagina_web.zona_id');
        $registro->liquida_impuestos = 1;
        $registro->condicion_pago_id = config('pagina_web.condicion_pago_id');
        $registro->estado = 'Activo';

        $registro->save();

        DireccionEntrega::create([
            'cliente_id' => $registro->id,
            'nombre_contacto' => $tercero->descripcion,
            'codigo_ciudad' => 16920001,
            'direccion1' => $datos['direccion1'],
            'barrio' => '',
            'codigo_postal' => '',
            'telefono1' => $datos['telefono1'],
            'datos_adicionales' => '',
            'por_defecto' => 1
        ]);

        // Crear usuario y asignar Perfil
        $name = $nombre1 . " " . $apellido1;
        $user = \App\User::create([
            'empresa_id' => 1,
            'name' => $name,
            'email' => $datos['email'],
            'password' => Hash::make( $datos['password'] )
        ]);

        $role_id = 16; // Cliente
        $role_r = \Spatie\Permission\Models\Role::where('id', '=', $role_id)->firstOrFail();
        $user->assignRole($role_r); //Assigning role to user

        // Asociar usuario al tercero
        $tercero->user_id = $user->id;
        $tercero->save();   
        auth()->loginUsingId($user->id);
    }
    
}
