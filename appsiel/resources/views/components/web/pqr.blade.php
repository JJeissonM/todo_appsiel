<link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">

<style type="text/css">
    #pqrform {

        <?php if ($registro !=null) {
            if ($registro->tipo_fondo=='COLOR') {
                echo "background-color: ". $registro->fondo . ";";
            } else {
                ?>background: url('{{$registro->fondo}}') {{$registro->repetir}} center {{$registro->direccion}};
                <?php
            }
        }

        ?>
    }

    .pqrform-font {
        @if( !is_null($registro)) @if( !is_null($registro->configuracionfuente)) font-family: <?php echo $registro->configuracionfuente->fuente->font;
        ?> !important;
        @endif @endif
    }

    #pqrform p{
        text-align: center;
    }
</style>

<div id="pqrform" class="container-wrapper pqrform-font">
    <div class="container">
        <div class="row" style="border-radius: 10px; background-color: white;">
            <div class="col-md-12">

                @include('layouts.mensajes')

                @if( !is_null($registro) )            

                <div class="col-sm-9">
                    {!! $registro->contenido_encabezado !!}
                 </div> 

                {{ Form::open(['url'=>'pqr_form/enviar','id'=>'formulario_pqr','method'=>'PUT','files' => true]) }}

                {!! $registro->get_lista_campos() !!}

                {{ Form::hidden('email_recepcion', $registro->parametros) }}
                {{ Form::hidden('fecha_hora', date('Y-m-d h:i a') ) }}
                {{ Form::hidden('widget_id', $registro->widget_id ) }}
                {{ Form::hidden('pagina_slug', $pagina->slug ) }}

                <div class="col-sm-9">
                   <input class="btn btn-primary form-control" type="submit" name="btn_enviar" value="Enviar mensaje"> 
                </div>                

                {{ Form::close() }}

                <div class="col-sm-9">
                    {!! $registro->contenido_pie_formulario !!}
                 </div>                 

                @endif

            </div>
        </div>
    </div>
</div>

<script src="{{asset('js/sweetAlert2.min.js')}}"></script>
<script type="text/javascript">
    document.getElementById('email').attributes[4].value = "email";

        function enviar_form( btn )
        {
            var url = $("#form_create").attr('action');
            var data = $("#form_create").serialize();

            $.post(url, data, function( respuesta ){
                console.log( respuesta );
            });
        }

</script>