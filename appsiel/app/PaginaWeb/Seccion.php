<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\PaginaWeb\Modulo;

class Seccion extends Model
{
    protected $table = 'pw_secciones';

	protected $fillable = ['titulo', 'mostrar_titulo', 'slug_id', 'detalle', 'padre_id','elementos','estado'];

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/pagina_web/seccion.js';

	public $encabezado_tabla = ['ID', 'Descripción', 'Detalle', 'Mostrar título', 'Sección padre', 'Estado', 'Acción'];
	
    public static function consultar_registros()
	{
	    return Seccion::leftJoin('pw_secciones AS seccion_padre','seccion_padre.id','=','pw_secciones.padre_id')
                            ->select(
                                        'pw_secciones.id AS campo1',
                                        'pw_secciones.titulo AS campo2',
                                        'pw_secciones.detalle AS campo3',
                                        'pw_secciones.mostrar_titulo AS campo4',
                                        DB::raw( 'CONCAT(seccion_padre.id," - ",seccion_padre.titulo) AS campo5' ),
                                        'pw_secciones.estado AS campo6',
                                        'pw_secciones.id AS campo7')
                    	    ->get()
                    	    ->toArray();
	}

    public static function get_datos_basicos( $seccion_id )
    {
        return Seccion::leftJoin('pw_secciones AS seccion_padre','seccion_padre.id','=','pw_secciones.padre_id')
                            ->leftJoin('pw_slugs','pw_slugs.id','=','pw_secciones.slug_id')
                            ->where( 'pw_secciones.id', $seccion_id)
                            ->select(
                                        'pw_secciones.id',
                                        'pw_secciones.titulo',
                                        'pw_secciones.detalle',
                                        'pw_secciones.slug_id',
                                        'pw_secciones.estado',
                                        'pw_slugs.slug',
                                        'pw_secciones.mostrar_titulo',
                                        DB::raw( 'CONCAT(seccion_padre.id," - ",seccion_padre.titulo) AS seccion_padre_descripcion' ),
                                        'pw_secciones.padre_id AS seccion_padre_id')
                            ->get()
                            ->first();
    }

    public static function get_slug( $seccion_id )
    {
        return Seccion::leftJoin('pw_slugs','pw_slugs.id','=','pw_secciones.slug_id')
                            ->where( 'pw_secciones.id', $seccion_id)
                            ->select(
                                        'pw_secciones.slug_id',
                                        'pw_slugs.slug')
                            ->get()
                            ->first();
    }



    public static function opciones_campo_select()
    {
        $opciones = Seccion::all();

        $vec['']='';
        foreach ($opciones as $opcion)
        {

            /*
              * Verificar ACL, para algunos modelos, se usa permiso_denegado == true (implica que hay que agregar true por defecto para todos los usuarios que vaya a utilizar el recurso), para otros se valida si permiso_denegado == false ( usuarios con restricciones al acceso del recurso)
            */
            $permiso_denegado = DB::table('core_acl')->where( 'modelo_recurso_id', 1 )
                            ->where( 'recurso_id', $opcion->id )
                            ->where( 'user_id', Auth::user()->id )
                            ->value( 'permiso_denegado' );

            if ( !$permiso_denegado ) 
            {
                
                /*
                    PENDIENTE POR VALIDAR CUANDO SE EDITA EL REGISTRO QUE SI DEBE APARACER LA OPCIÓN
                    
                // Una sección puede tener máximo 4 hijas (Para crear las columnas automáticas con bootstrap: 12/cantidad_hijas )


                $cantidad_seccion_hijas = Seccion::where( 'padre_id', $opcion->id)->count();

                if ( $cantidad_seccion_hijas < 4)
                {
                    $vec[$opcion->id] = $opcion->descripcion.' ('.$opcion->estado.')';
                }
                */

                $vec[$opcion->id] = $opcion->titulo.' ('.$opcion->estado.')';
            }
        }
        
        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"pw_secciones",
                                    "llave_foranea":"padre_id",
                                    "mensaje":"Esta es una sección padre. Está asociada a una o más secciones."
                                },
                            "1":{
                                    "tabla":"pw_modulos",
                                    "llave_foranea":"seccion_id",
                                    "mensaje":"Sección está asociada a uno o más Módulos."
                                },
                            "2":{
                                    "tabla":"pw_pagina_tiene_seccion",
                                    "llave_foranea":"seccion_id",
                                    "mensaje":"Sección está asociada a una Página."
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


    public static function get_campos_adicionales_edit( $lista_campos, $registro )
    {
        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'slug':
                    $lista_campos[$i]['value'] = Slug::find( $registro->slug_id )->slug;
                    break;

                default:
                    # code...
                break;
            }
        }

        return $lista_campos;
    }

    public static function show_adicional( $lista_campos, $registro )
    {
        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'slug':
                    $lista_campos[$i]['value'] = Slug::find( $registro->slug_id )->slug;
                    break;

                default:
                    # code...
                break;
            }
        }

        return $lista_campos;
    }

    public static function store_adicional( $datos, $registro )
    {
        // Almacenar Slug
        $datos['name_space_modelo'] = 'App\PaginaWeb\Seccion';
        $datos['estado'] = 'Activo';
        $slug = Slug::create( $datos );
        
        // Actualizar artículo creado
        $registro->slug_id = $slug->id;
        $registro->save();
    }

    public static function update_adicional( $datos, $id )
    {
        $registro = Seccion::find( $id );
        
        // Actualizar Slug
        Slug::find( $registro->slug_id )->update( $datos );
    }
}
