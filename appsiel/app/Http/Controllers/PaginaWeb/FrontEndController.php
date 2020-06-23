<?php

namespace App\Http\Controllers\PaginaWeb;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Controllers\web\PaginaController;
use Input;
use View;
use DB;

use App\Sistema\Aplicacion;
use App\Core\Empresa;

use App\PaginaWeb\Micrositio;
use App\PaginaWeb\Pagina;
use App\PaginaWeb\Articulo;
use App\PaginaWeb\Carousel;
use App\PaginaWeb\Categoria;

use App\PropiedadHorizontal\PhAnuncio;

class FrontEndController extends Controller
{

    // Llama al index de la plantilla default
    public function inicio()
    {

        // Se verifica que la Aplicación Página Web esté activa
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

            $pagina = Pagina::find(Input::get('pagina_id'));
        }

        if($pagina == null)
            return view('pagina_no_encontrada', ['slug'=>''] );

        $page = new PaginaController();
        return $page->showPage($pagina->slug);
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