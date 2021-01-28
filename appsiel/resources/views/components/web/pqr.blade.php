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

    #pqrform .col-sm-9, #pqrform .col-md-9{
        flex: 0 0 auto;
        max-width: 100%;
    }

</style>

<div id="pqrform" class="container-wrapper pqrform-font p-md-5 p-sm-2">
    <div class="container-fluid">
        <div class="row justify-content-center py-5" style="border-radius: 10px; background-color: white; ">
            <div class="col-md-6" style="max-width: 800px;">

                @include('layouts.mensajes')

                @if( !is_null($registro) )            

                <div class="col-sm-9 section-header">

                    <h2 class="text-center section-title pqrform-font">{!! $registro->contenido_encabezado !!}</h2>
                 </div> 

                {{ Form::open(['url'=>'pqr_form/enviar','id'=>'formulario_pqr','method'=>'PUT','files' => true]) }}
                 <div class="pqrform-font">
                    {!! $registro->get_lista_campos() !!}
                 </div>
                

                {{ Form::hidden('email_recepcion', $registro->parametros) }}
                {{ Form::hidden('fecha_hora', date('Y-m-d h:i a') ) }}
                {{ Form::hidden('widget_id', $registro->widget_id ) }}
                {{ Form::hidden('pagina_slug', $pagina->slug ) }}

                <div class="col-sm-9">
                   <input class="btn btn-primary form-control" type="submit" name="btn_enviar" value="Enviar mensaje"> 
                </div>                

                {{ Form::close() }}

                <div class="col-sm-9 py-3">
                    <h2 class="pqrform-font ">{!! $registro->contenido_pie_formulario !!}</h2>                    
                 </div>                 

                @endif

            </div>
        </div>
    </div>
</div>

<script src="{{asset('js/sweetAlert2.min.js')}}"></script>
<script type="text/javascript">
    //document.getElementById('email').value = "email";

        function enviar_form( btn )
        {
            var url = $("#form_create").attr('action');
            var data = $("#form_create").serialize();

            $.post(url, data, function( respuesta ){
                console.log( respuesta );
            });
        }

</script>