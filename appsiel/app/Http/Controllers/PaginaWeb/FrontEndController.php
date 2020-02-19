<?php

namespace App\Http\Controllers\PaginaWeb;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Input;
use View;
use DB;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\PaginaWeb\Micrositio;
use App\PaginaWeb\Pagina;
use App\PaginaWeb\Seccion;
use App\PaginaWeb\Modulo;
use App\PaginaWeb\Articulo;
use App\PaginaWeb\Carousel;
use App\PaginaWeb\Categoria;

use App\PropiedadHorizontal\PhAnuncio;

class FrontEndController extends Controller
{
    
    // Llama al index de la plantilla default
    public function inicio()
    {

        // Se verifica que la Aplicación Página Web esté activao
        $estado_pagina_web = Aplicacion::where('app','pagina_web')->value('estado');        
        if ($estado_pagina_web == 'Inactivo')
        {
            return redirect('inicio');
        }

        

        // Se continua si la aplicación página web está activa

        // Obtener la página que está marcada como pagina_inicio (se debe validar que en la creación de páginas solo haya una)
        $pagina = Pagina::where('pagina_inicio',1)->get()->first();

        // Si se envía el ID de una página, por url
        if ( !is_null( Input::get('pagina_id') ) )
        {
            $pagina = Pagina::find( Input::get('pagina_id') );
        }
        
        
        // Return temporal para mostrar página estática de información
        return View::make( 'pagina_web.front_end.templates.demo.index', compact('pagina') )->render();

        

        /*
          * SECCIONES
        */
        $secciones_pagina = $pagina->secciones()->where('estado','Activo')->orderBy('orden')->get();
        
        $cadena_secciones = '';
        foreach ($secciones_pagina as $una_seccion) 
        {
            if ($una_seccion->padre_id == 0) 
            {
                $datos_seccion = Seccion::get_datos_basicos( $una_seccion->id );

                $titulo = '<span class="titulo_seccion">'.$una_seccion->titulo.'</span>';
                if ( !$una_seccion->mostrar_titulo ) 
                {
                    $titulo = '';
                }
                
                $cadena_secciones .= '<div class="seccion_padre" id="'.$datos_seccion->slug.'"> '.$titulo.'_seccion_'.$una_seccion->id.'_contenido </div>';
            }

            $hijas = Seccion::where('padre_id',$una_seccion->id)->get();

            if ( !empty( $hijas->toArray() ) ) 
            {

                $cantidad_hijas = count( $hijas->toArray() );
                
                // Restringir a solo 4 hijas. Esto no se debe hacer de este modo. Mejorar el modelo Seccion
                if ( $cantidad_hijas > 4 )
                {
                    $cantidad_hijas = 4;
                }
                
                $numero_columnas = 12 / $cantidad_hijas;

                $secciones_hijas = '';
                foreach ($hijas as $una_hija) 
                {
                    $datos_seccion = Seccion::get_datos_basicos( $una_hija->id );

                    $titulo = '<span class="titulo_seccion">'.$una_hija->titulo.'</span>';
                    if ( !$una_hija->mostrar_titulo ) 
                    {
                        $titulo = '';
                    }
                    
                    $secciones_hijas .= '<div class="seccion_hija col-md-'.$numero_columnas.'" id="'.$datos_seccion->slug.'"> '.$titulo.'_seccion_'.$una_hija->id.'_contenido </div>';
                }

                $cadena_secciones = str_replace( '_seccion_'.$una_seccion->id.'_contenido', '<div class="row">'.$secciones_hijas.'</div>' , $cadena_secciones);            
            }
        }


        /*
          * MODULOS: son los fragmentos de código HTML que forman el contenidos de la página
          * Se obtienen los módulos 
        */
        $modulos = Modulo::get_datos_basicos(); 

        // Se crea un array con la lista de módulos
        foreach ($modulos as $un_modulo) 
        {
            $titulo = '<span class="titulo_modulo">'.$un_modulo->descripcion.'</span>';
            if ( !$un_modulo->mostrar_titulo ) 
            {
                $titulo = '';
            }

            // Se llena el contenido del módulo con el campo "contenido" de la tabla pw_modulos
            $contenido_modulo = '<div class="modulo" id="modulo_'.$un_modulo->id.'"> '.$titulo.$un_modulo->contenido.' </div>';

            // Si el tipo de módulo maneja Clase (campo modelo), se reemplaza el contenido con el contenido generado por esa clase, se sobreescribe la variable $contenido_modulo
            if ( $un_modulo->modelo != '') 
            {
                // Se crea un nuevo objeto para el tipo de módulo
                $obj_modulo = new $un_modulo->modelo();

                $obj_modulo->asignar_parametros( $un_modulo->parametros ); // WARNING: TODOS las clase de módulos deben manejar los mismos métodos, se debe utilizar una interface para esto

                $contenido_modulo = '<div class="modulo" id="modulo_'.$un_modulo->id.'"> '.$titulo.$obj_modulo->dibujar_modulo()->render().' </div>';

            }
            
            $cadena_secciones = str_replace( '_seccion_'.$un_modulo->seccion_id.'_contenido', $contenido_modulo, $cadena_secciones);

        }


        $pagina_plantilla = 'default';

        return view( 'pagina_web.front_end.templates.'.$pagina_plantilla.'.index', compact( 'pagina', 'cadena_secciones' ) );
        
    }

    

    public function mostrar_enlace( $slug )
    {
        $articulo = Articulo::where('slug',$slug)->get()->first();

        if ( is_null( $articulo ) )
        {
            return view('pagina_no_encontrada', compact('slug') );
        }

        
    }



    // Llama al index de blog
    public function blog( $slug = null )
    {        
        $pagina = Pagina::find(1);
        $empresa = Empresa::find(1);

        if ( $slug == 'galeria_imagenes') 
        {
            $miga_pan = [
                        ['url'=>'/', 'etiqueta'=>'Inicio'],
                        ['url'=>'NO', 'etiqueta'=> 'Galería de imágenes']
                    ];
            
            $galeria_imagenes = true;

            return view('pagina_web.front_end.templates.blog.index',compact('pagina', 'empresa','miga_pan','galeria_imagenes'));

        }else{

            if ( $slug != null ) {
                $el_articulo = Articulo::where('slug',$slug)->get()[0];
                $categoria_id = $el_articulo->categoria_id;
            }else{
                $categoria_id = 1;
                $el_articulo = Articulo::where('estado','Activo')->where('categoria_id',$categoria_id)->orderBy('id', 'desc')->take(1)->get()[0];
            }            

            $articulos = Articulo::where('estado','Activo')->where('categoria_id',$categoria_id)->orderBy('id', 'desc')->get();

            $miga_pan = [
                        ['url'=>'/', 'etiqueta'=>'Inicio'],
                        ['url'=>'NO', 'etiqueta'=>$el_articulo->titulo]
                    ];

            $categoria = Categoria::find($categoria_id);

            $galeria_imagenes = false;

            return view('pagina_web.front_end.templates.blog.index',compact('pagina', 'empresa','articulos','el_articulo','miga_pan','galeria_imagenes','categoria'));
        }

    }

    

    // Llama al index de blog
    public function show_categoria( $categoria_id )
    {        
        $pagina = Pagina::find(1);
        $empresa = Empresa::find(1); 

        $articulos = Articulo::where('estado','Activo')->where('categoria_id',$categoria_id)->orderBy('id', 'desc')->get();
        
        $el_articulo = Articulo::where('estado','Activo')->where('categoria_id',$categoria_id)->orderBy('id', 'desc')->take(1)->get()[0];

        $miga_pan = [
                    ['url'=>'/', 'etiqueta'=>'Inicio'],
                    ['url'=>'NO', 'etiqueta'=>$el_articulo->titulo]
                ];

        $categoria = Categoria::find($categoria_id);

        $galeria_imagenes = false;

        return view('pagina_web.front_end.templates.blog.index',compact('pagina', 'empresa','articulos','el_articulo','miga_pan','galeria_imagenes','categoria'));

    }


    public function contactenos( Request $request )
    {
        $pagina = Pagina::find(1);
        $empresa = Empresa::find(1);

        // Email interno. Debe estar creado en Hostinger
        $email_interno = 'info@'.substr( url('/'), 7);
        
        // Datos requeridos por hostinger
        $from = "Pagina Web <".$email_interno."> \r\n";
        $headers = "From:" . $from." \r\n";
        $to = $empresa->email;
        $subject = "Comentario desde la pagina web";
        
        // El mensaje
        $cuerpo_mensaje = View::make('pagina_web.front_end.modulos.contactenos.cuerpo_mensaje',compact('request','empresa'))->render();

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"=A=G=R=O=\"\r\n\r\n";


        // Armando mensaje del email
        $message = "--=A=G=R=O=\r\n";
        $message .= "Content-type:text/html; charset=utf-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $cuerpo_mensaje . "\r\n\r\n";
         
        //if(true)
        if (mail($to,$subject,$message, $headers))
        {
            $alerta = '<div class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                          <strong>¡El mensaje se ha enviado!</strong> Nos comunicaremos con usted los más pronto posible.
                        </div>';
        } else {
            $alerta = '<div class="alert alert-danger alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                          <strong>¡El mensaje no pudo ser enviado!</strong> Por favor intente nuevamente. Si el problema persiste, intente más tarde.
                        </div>';
        }

        return $alerta;
    }
    

    public function micrositio($id)
    {
        $registro = Micrositio::find($id);

        $anuncios = PhAnuncio::where('core_empresa_id',$registro->core_empresa_id)->where('estado','Activo')->get();

        if ( count($registro) > 0 ) {
            return view('pagina_web_asiph675.micrositios.index', compact('registro','anuncios') );
        }else{
            echo "Micrositio no encontrado.<br/>";
            print_r( $registro );
            echo "<br/>".count($registro);
        }
        // Pendiente si el micrositio o existe, pagina de alerta
    }

    public function ajax_galeria_imagenes( $carousel_id ){
        $datos_carousel = Carousel::get_array_datos( $carousel_id );
        return '<h3>'.$datos_carousel['descripcion'].'</h3>'.View::make( 'pagina_web.front_end.modulos.carousel.index',['datos'=>$datos_carousel] );
    }

}