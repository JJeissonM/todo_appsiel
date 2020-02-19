<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

use DB;

class Modulo extends Model
{
    protected $table = 'pw_modulos';

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/pagina_web/modulos/principal_modulos.js';

	protected $fillable = ['descripcion', 'detalle', 'contenido', 'parametros', 'orden', 'seccion_id', 'mostrar_titulo', 'tipo_modulo', 'estado'];

	public $encabezado_tabla = ['ID', 'Descripción', 'Detalle', 'Orden', 'Sección', 'Mostrar título', 'Tipo módulo', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = Modulo::leftJoin('pw_secciones','pw_secciones.id','=','pw_modulos.seccion_id')
	    					->leftJoin('pw_tipos_modulos','pw_tipos_modulos.id','=','pw_modulos.tipo_modulo')
	    					->select(
	    								'pw_modulos.id AS campo1',
	    								'pw_modulos.descripcion AS campo2',
	    								'pw_modulos.detalle AS campo3',
	    								'pw_modulos.orden AS campo4',
	    								DB::raw( 'CONCAT(pw_secciones.id," - ",pw_secciones.titulo) AS campo5' ),
	    								'pw_modulos.mostrar_titulo AS campo6',
	    								'pw_tipos_modulos.descripcion AS campo7',
	    								'pw_modulos.estado AS campo8',
	    								'pw_modulos.id AS campo9')
						    ->get()
						    ->toArray();
	    return $registros;
	}




    public static function get_datos_basicos( $modulo_id = null )
    {
    	$array_wheres = [ 
    						['pw_modulos.id', '>', 0 ],
    						'pw_modulos.estado' => 'Activo',
    						 'pw_secciones.estado' => 'Activo'
    					]; // todos los registros activos

        if ( !is_null($modulo_id) )
        {
            $array_wheres = array_merge($array_wheres, ['pw_modulos.id' => $modulo_id] );
        }

    	return Modulo::leftJoin('pw_tipos_modulos','pw_tipos_modulos.id','=','pw_modulos.tipo_modulo')
    					->leftJoin('pw_secciones','pw_secciones.id','=','pw_modulos.seccion_id')
    					->where( $array_wheres )
    					->select(
    								'pw_tipos_modulos.descripcion AS descripcion_tipo',
    								'pw_tipos_modulos.modelo',
    								'pw_modulos.id',
    								'pw_modulos.descripcion',
    								'pw_modulos.contenido',
    								'pw_modulos.parametros',
    								'pw_modulos.orden',
    								'pw_modulos.seccion_id',
    								'pw_modulos.mostrar_titulo',
    								'pw_modulos.imagen',
    								'pw_modulos.estado')
    					->orderBy('pw_modulos.orden')
    					->get();
    }

}