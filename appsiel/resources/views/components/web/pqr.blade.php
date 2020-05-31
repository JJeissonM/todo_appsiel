<link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
<div class="container-wrapper">
    <div class="container">
        <div class="row" style="border-radius: 10px; background-color: white;">
            <div class="col-md-12">

                @include('layouts.mensajes')

                @if( !is_null($registro) )
                    
                    {!! $registro->contenido_encabezado !!}

                    {{ Form::open(['url'=>'pqr_form/enviar','id'=>'formulario_pqr','method'=>'PUT','files' => true]) }}
                        
                        {!! $registro->get_lista_campos() !!}

                        {{ Form::hidden('email_recepcion', $registro->parametros) }}
                        {{ Form::hidden('fecha_hora', date('Y-m-d h:i a') ) }}
                        {{ Form::hidden('widget_id', $registro->widget_id ) }}
                        {{ Form::hidden('pagina_slug', $pagina->slug ) }}

                        <input class="btn btn-primary" type="submit" name="btn_enviar" value="Enviar mensaje">

                    {{ Form::close() }}

                    {!! $registro->contenido_pie_formulario !!}

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