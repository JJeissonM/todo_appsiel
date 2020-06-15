<style>
    .social {
        position: fixed;
        /* Hacemos que la posición en pantalla sea fija para que siempre se muestre en pantalla*/
        /* Establecemos la barra en la posición indicada */
        @if($sticky!=null)
        @if($sticky->posicion=='IZQUIERDA')
        left: 0;
        top: 200px;
        /* Bajamos la barra 200px de arriba a abajo */
        @endif
        @if($sticky->posicion=='DERECHA')
        right: 0;
        top: 200px;
        /* Bajamos la barra 200px de arriba a abajo */
        @endif
        @else
        left: 0;
        top: 200px;
        /* Bajamos la barra 200px de arriba a abajo */
        @endif
        z-index: 2000;
        /* Utilizamos la propiedad z-index para que no se superponga algún otro elemento como sliders, galerías, etc */
    }

    .social ul {
        list-style: none;
    }

    .social ul li a {
        width: @if($sticky!=null) {{$sticky->ancho_boton}}px @else 50px @endif;
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
        background: #000;
        /* Cambiamos el fondo cuando el usuario pase el mouse */
        @if($sticky!=null)
        @if($sticky->posicion=='DERECHA')
        padding: 50px 15px;
        @else
        padding: 15px 50px;
        @endif
        @endif
        /* Hacemos mas grande el espacio cuando el usuario pase el mouse */
    }
</style>

<div class="social">
    <ul style="list-style: none !important;">
        @if($sticky!=null)
        @if(count($sticky->stickybotons)>0)
        @foreach($sticky->stickybotons as $b)
        <li><a @if($b->texto!=null) data-toggle="tooltip" data-placement="right" title="{{$b->texto}}" @endif @if($b->enlace!=null) href="{{$b->enlace}}" @endif target="_blank" style="background-color: {{$b->color}};"> @if($b->icono!=null) <i class="fa fa-{{$b->icono}}"></i> @endif</a></li>
        @endforeach
        @endif
        @endif
    </ul>
</div>
<script type="text/javascript">

</script>