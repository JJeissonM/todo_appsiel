<style>
    .stickies-font {
        @if( !is_null($sticky)) @if( !is_null($sticky->configuracionfuente)) font-family: <?php echo $sticky->configuracionfuente->fuente->font;
        ?> !important;
        @endif @endif
    }

    .social {
        position: fixed;
        /* Hacemos que la posición en pantalla sea fija para que siempre se muestre en pantalla*/
        /* Establecemos la barra en la posición indicada */
        @if($sticky !=null) @if($sticky->posicion=='IZQUIERDA') left: 0;
        top: 200px;
        /* Bajamos la barra 200px de arriba a abajo */
        @endif @if($sticky->posicion=='DERECHA') right: 0;
        top: 200px;
        /* Bajamos la barra 200px de arriba a abajo */
        @endif @else left: 0;
        top: 200px;
        /* Bajamos la barra 200px de arriba a abajo */
        @endif z-index: 2000;
        /* Utilizamos la propiedad z-index para que no se superponga algún otro elemento como sliders, galerías, etc */
    }

    .social ul li {
        list-style: none;
    }

    .social ul li a {
        width: @if($sticky !=null) {{$sticky->ancho_boton}}px 
        @else 50px @endif;
        display: inline-block;
        color: #fff;
        background: #000;
        padding: 15px 20px;
        text-decoration: none;
        -webkit-transition: all 500ms ease;
        -o-transition: all 500ms ease;
        transition: all 500ms ease;
        /* Establecemos una transición a todas las propiedades */
    }

    .social ul li a:hover {        
        /* Cambiamos el fondo cuando el usuario pase el mouse */
        @if($sticky !=null) @if($sticky->posicion=='DERECHA') transform: scale(1.4);
        @else transform: scale(1.4);
        @endif 
        @endif
        /* Hacemos mas grande el espacio cuando el usuario pase el mouse */
    }

        

</style>

<div class="social stickies-font">
    <ul>
        @if($sticky!=null)
        @if(count($sticky->stickybotons)>0)
        @foreach($sticky->stickybotons as $b)
        <li>
            <a @if($b->texto!=null) data-toggle="tooltip" data-placement="right" title="{{$b->texto}}" @endif
                @if($b->enlace!=null) href="{{$b->enlace}}" @endif target="_blank" style="background-color:
                {{$b->color}}; opacity: {{$b->alpha/10}}"> @if($b->icono!=null) <i class="fa fa-{{$b->icono}}"></i> @endif
                @if($b->imagen!=null)
                <img style="width: 100px; max-height: 100px;" src="{{ asset('docs/'.$b->imagen)}}" />
                @endif
            </a>
        </li>
        @endforeach
        @endif
        @endif
    </ul>
</div>
<script type="text/javascript">

</script>