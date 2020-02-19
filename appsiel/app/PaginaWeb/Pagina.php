<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use View;

use App\PaginaWeb\PaginaTieneSeccion;
use App\PaginaWeb\Seccion;

class Pagina extends Model
{
    protected $table = 'pw_paginas';

    protected $fillable = ['descripcion', 'meta_description', 'meta_keywords', 'codigo_google_analitics', 'favicon', 'titulo', 'logo', 'pagina_inicio', 'estado'];

    public $encabezado_tabla = ['ID','Descripción','Título','Cod. Google Analitics','Estado','Acción'];

    public $ruta_storage_files = 'pagina_web/';

    public static function consultar_registros()
    {
    	$registros = Pagina::select(
                                    'pw_paginas.id AS campo1',
                                    'pw_paginas.descripcion AS campo2',
                                    'pw_paginas.titulo AS campo3',
                                    'pw_paginas.codigo_google_analitics AS campo4',
                                    'pw_paginas.estado AS campo5',
                                    'pw_paginas.id AS campo6')
            ->get()
            ->toArray();

        return $registros;
    }

    public function secciones()
    {
        return $this->belongsToMany('App\PaginaWeb\Seccion','pw_pagina_tiene_seccion','pagina_id','seccion_id');
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso pw_secciones
    */

        // Tabla para visualizar registros asignados (hijos)
        // En la vista show del modelo padre
    public static function get_tabla($registro_modelo_padre,$registros_asignados)
    {
        $encabezado_tabla = ['Orden','ID','Descripción','Acción'];

        $registros = [];
        $i = 0;
        foreach($registros_asignados as $fila)
        {
            $orden = PaginaTieneSeccion::where('seccion_id', '=', $fila['id'])
                                        ->where('pagina_id', '=', $registro_modelo_padre->id)
                                        ->value('orden');

            $registros[$i] = collect( [ 
                                        $orden,
                                        $fila['id'],
                                        $fila['titulo']
                                    ]);
            $i++;
        }

        return View::make( 'core.modelos.tabla_modelo_relacionado', compact('encabezado_tabla','registros','registro_modelo_padre') )->render();
    }

    // Opciones del select para asignar nuevos hijos
    public static function get_opciones_modelo_relacionado($pagina_id)
    {
        $vec['']='';
        // Solo se muestran las secciones padre
        $opciones = Seccion::where('padre_id',0)->get();
        
        foreach ($opciones as $opcion)
        {
            $esta = PaginaTieneSeccion::where('pagina_id',$pagina_id)->where('seccion_id',$opcion->id)->get();

            if ( empty( $esta->toArray() ) )
            {
                $vec[$opcion->id] = $opcion->titulo;
            }

        }
        return $vec;
    }


    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'pw_pagina_tiene_seccion';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'pagina_id';
        $registro_modelo_hijo_id = 'seccion_id';

        return compact('nombre_tabla','nombre_columna1','registro_modelo_padre_id','registro_modelo_hijo_id');
    }


    public function get_url_favicon()
    {
        $url = config('configuracion.url_instancia_cliente')."/favicon.ico";

        if( $this->favicon != '' )
        {
            $url = config('configuracion.url_instancia_cliente')."/storage/app/".$this->ruta_storage_files.$this->favicon;
        }

        return $url;
    }


    public static function opciones_campo_select()
    {
        $opciones = Pagina::where('estado','=','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion;
        }
        
        return $vec;
    }
}
