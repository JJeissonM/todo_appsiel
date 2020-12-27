<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

class Articulo extends Model
{
    protected $table = 'pw_articulos'; 

    protected $fillable = ['titulo', 'slug_id', 'resumen', 'palabras_claves', 'contenido_articulo', 'imagen', 'user_id', 'categoria_id', 'mostrar_titulo', 'estado', 'creado_por', 'modificado_por'];

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/pagina_web_articulos.js';

    public $encabezado_tabla = ['ID', 'Título','Resumen','Categoría', 'Palabras claves','Estado','Acción'];

    public static function consultar_registros()
    {

    	$registros = Articulo::leftJoin('pw_categorias','pw_categorias.id','=','pw_articulos.categoria_id')
            ->select('pw_articulos.id AS campo1','pw_articulos.titulo AS campo2','pw_articulos.resumen AS campo3','pw_categorias.descripcion AS campo4','pw_articulos.palabras_claves AS campo5','pw_articulos.estado AS campo6','pw_articulos.id AS campo7')
            ->get()
            ->toArray();

        return $registros;
    }

    public function user()
    {
        return $this->belongsTo('App\User')->withTrashed();
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
    
    public function categoria()
    {
        return $this->belongsTo(Categoria::class)->withTrashed();
    }


    public static function opciones_campo_select()
    {
        $opciones = Articulo::where('estado','=','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->titulo;
        }
        
        return $vec;
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
        $datos['name_space_modelo'] = 'App\PaginaWeb\Articulo';
        $datos['estado'] = 'Activo';
        $slug = Slug::create( $datos );
        
        // Actualizar artículo creado
        $registro->slug_id = $slug->id;
        $registro->save();
    }

    public static function update_adicional( $datos, $id )
    {
        $registro = Articulo::find( $id );

        // Actualizar Slug
        Slug::find( $registro->slug_id )->update( $datos );
    }
}
