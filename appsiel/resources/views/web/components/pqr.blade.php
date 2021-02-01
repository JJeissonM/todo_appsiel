@extends('web.templates.main')

@section('style')
<style>
    .card-body {
        padding: 0 !important;
        overflow: hidden;
    }

    #wrapper {
        overflow-y: scroll;
        overflow-x: hidden;
        width: 30%;
        margin-right: 0;
        padding: 5px;
    }

    .list-group-item {
        background-color: transparent;
        font-size: 16px;
    }

    .list-group-item:hover {
        background-color: #3d6983;
        color: white;
        cursor: pointer;
    }

    .widgets {
        width: 70%;
    }

    .widgets img {
        width: 100%;
        object-fit: cover;
        height: 72.5vh;
        max-width: 100%;
    }

    .widgets .card-body {
        position: relative;
    }

    .activo {}

    .descripcion {
        padding: 5px;
    }

    .descripcion h5 {
        color: black;
        font-size: 16px;
    }

    .add {
        margin-top: 20px;
    }

    .add a {
        color: #1c85c4;
    }

    #form_create label {
        background: #ddd;
        width: 100%;
        margin-bottom: 5px;
    }
</style>

@endsection

<?php
    use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: {{$widget->seccion->nombre}} ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="body d-flex justify-content-between flex-wrap">
        <div id="wrapper">

            <h4 class="column-title" style="padding: 10px;"> Configurar {{$widget->seccion->nombre}}</h4>
            @if($registro != null)
            {{ Form::model($registro, ['url' => 'pqr_form/'.$registro->id, 'method' => 'PUT','id'=>'form_create','files' => true]) }}
            <?php
                        $contenido_encabezado = $registro->contenido_encabezado;
                        $contenido_pie_formulario = $registro->contenido_pie_formulario;
                        $campos_mostrar = $registro->campos_mostrar;
                        $parametros = $registro->parametros;
                    ?>
            @else
            {{ Form::open(['url'=>'pqr_form','id'=>'form_create','files' => true]) }}
            <?php
                        $contenido_encabezado = '';
                        $contenido_pie_formulario = '';
                        $campos_mostrar = '';
                        $parametros = '';
                    ?>
            @endif

            <div class="form-group">
                <label for="contenido_encabezado" class="col-form-label">Contenido del encabezado <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <textarea name="contenido_encabezado" class="form-control contenido"
                    id="contenido_encabezado">{{$contenido_encabezado}}</textarea>
            </div>


            <div class="form-group">
                <label for="contenido_pie_formulario" class="col-form-label">Contenido al pie del formulario<span data-toggle="tooltip" title="Establece el pie de foumulario de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <textarea name="contenido_pie_formulario" class="form-control contenido"
                    id="contenido_pie_formulario">{{$contenido_pie_formulario}}</textarea>
            </div>

            <!-- ESTE CAMPO SE AMACENA EN FORMATO JSON EN EL CAMPO PARAMETROS DE LA TABLA PW_PQR_FORM -->
            <div class="form-group">
                <label for="parametros" class="col-form-label">E-mail donde recibir mensajes<span data-toggle="tooltip" title="Establece el e-mail donde recibira los PQR."> <i class="fa fa-question-circle"></i></span></label>                
                <input type="email" class="form-control" name="parametros" id="name" value="{{$parametros}}">
            </div>

            <?php 
                    $filas = '';
                    if ( $campos_mostrar != '' ) 
                    {
                        $opciones = json_decode( $campos_mostrar );
                        if ( !is_null($opciones) ) 
                        {
                            foreach ($opciones as $key => $value) {
                                $filas .= '<tr>
                                            <td>'.$key.'</td>
                                            <td>'.$value.'</td>
                                            <td><button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-trash"></i></button></td>
                                            </tr>';
                            }
                        }                      
                    }

                    $tabla = ' <div class="form-group"> <div id="div_agregar_opciones" class="well">
                                    <br/>
                                    <h4> Campos a mostrar </h4>
                                    <hr>
                                    <table class="table table-bordered" id="ingreso_registros">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Descripción</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        '.$filas.'
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td><input type="text" id="key_json" style=" width:35px;" readonly></td>
                                                <td><input type="text" id="value_json" readonly></td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-success" id="btn_nueva_linea"><i class="fa fa-btn fa-plus"></i></button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                </div>';

                    ?>

            {!! $tabla !!}

            <input type="hidden" class="form-control" name="campos_mostrar" id="campos_mostrar" style="background:cyan;"
                value="{{$campos_mostrar}}">

            <div class="form-group">
                <label for="campo_id" class="col-form-label">Seleccionar Campo<span data-toggle="tooltip" title="Establece los campos para añadir a la sección. (Pulse el boton <button type='button' class='btn btn-xs btn-success'><i class='fa fa-btn fa-plus'></i></button> Luego de seleccionar el campo)."> <i class="fa fa-question-circle"></i></span></label>                
                {{ Form::select( 'campo_id', $campos, null, ['class'=>'form-control select2-search','id'=>'campo_id'] ) }}
            </div>

            {{ Form::hidden('widget_id', $widget->id) }}
            {{ Form::hidden('url_id',Input::get('id')) }}

            <div class="form-group">
                <label for="">Fuente Para el Componente <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                @if($fonts!=null)
                {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control
                select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                @endif
            </div>
            <div class="form-group">
                <label>¿El fondo es Imagen o Color? <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span></label>                
                <select type="select" class="form-control" id="tipo_fondo" required name="tipo_fondo"
                    onchange="cambiar()">
                    <option value="">-- Seleccione una opción --</option>
                    <option value="IMAGEN">IMAGEN</option>
                    <option value="COLOR">COLOR</option>
                </select>
            </div>
            <div class="form-group" id="fondo_container">
            </div>

            <div class="form-group">
                <br /><br />
                {{ Form::bsButtonsForm( 'paginas?id=' . Input::get('id') ) }}
            </div>

            {{ Form::close() }}
        </div>

        <div class="widgets" id="widgets" style="position: relative;">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            {!! Form::pqr( $registro, $pagina )!!}
        </div>

    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">

$(document).ready(function() {
    $('.select2-search').select2();
});


    $(document).ready(function(){

            $('#bs_boton_guardar').on('click',function(event){
                event.preventDefault();

                // Desactivar el click del botón
                $( this ).off( event );

                $('#form_create').submit();
            });

            // Al cambiar el tipo de campo
            $('#campo_id').on('change', function()
            {
                $('#key_json').val( $('#campo_id').val() );
                $('#value_json').val( $('#campo_id option:selected').text() );
            });

            // Para el campo tipo json_simple
            $('#btn_nueva_linea').click(function(e)
            {
                e.preventDefault();
                if ( $('#key_json').val() != '' && $('#value_json').val() != '' ) 
                {
                    // Se agrega una nueva línea a la tabla de opciones
                    var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-trash'></i></button>";
                    $('#ingreso_registros').find('tbody').append('<tr> <td>' + $('#key_json').val() +'</td> <td>'+ $('#value_json').val() +'</td><td>'+btn_borrar+'</td></tr>');
                    
                    // Se agrega el valor de la opción ingresada a la lista de respuestas
                    $('#lista_respuestas').append($('<option>', { value: $('#key_json').val(), text: $('#key_json').val()}));

                    asignar_opciones();

                    // Se Vacían las cajas de texto
                    $('#key_json').val('');
                    $('#value_json').val('');

                    $('#key_json').focus();

                }else{
                    alert('Debe ingresar una opción y su valor.');
                    $('#key_json').focus();
                }
            });

            $(document).on('click', '.btn_eliminar', function() {
                var fila = $(this).closest("tr");
                var key_json_tabla = fila.find('td:first').html();
                
                $("#lista_respuestas option[value='"+key_json_tabla+"']").remove();
                fila.remove();

                asignar_opciones();
            });


            /*
              * Se va crear una cadena en formato JSON con cada una de las filas de la tabla de opciones
            */
            function asignar_opciones() 
            {
                
                var text = '{ ';

                var primero = true;
                $('#ingreso_registros').find('tbody>tr').each( function(){
                    var key_json = $(this).find('td:first').html();
                    var value_json = $(this).find('td:first').next('td').html();

                    if ( primero ) {
                        text = text + '"'+key_json+'":"'+value_json+'"';
                        primero = false;
                    }else{
                        text = text + ', "'+key_json+'":"'+value_json+'"';
                    }
                    
                });

                var text = text + '}';

                $('#campos_mostrar').val( text );
            }

            $('.contenido').on('focus', function () {

                original_name = $(this).attr('name');

                $(this).attr('name', 'contenido');

                CKEDITOR.replace('contenido', {
                    height: 200,
                    // By default, some basic text styles buttons are removed in the Standard preset.
                    // The code below resets the default config.removeButtons setting.
                    removeButtons: ''
                });

            });

            $('.contenido').on('blur', function () {

                $(this).attr('name', original_name);

            });

        });

        function cambiar() {
        $("#fondo_container").html("");
        var f = $("#tipo_fondo").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + `Imagen de Fondo <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='file' class='form-control' name='fondo' required>` +
                `<label>Repetir <span data-toggle="tooltip" title="Establece si la imagen se repite en el fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>`+
                `<label>Orientación Imagen <span data-toggle="tooltip" title="Establece la orientacion de la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>
                <select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option><option value='top'>ARRIBA</option></select>`;
        } else if (f == 'COLOR') {
            html = html + `Color de Fondo <span data-toggle="tooltip" title="Establece el color de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='color' class='form-control' name='fondo' required>`;
        } else {
            html = "";
        }
        $("#fondo_container").html(html);
        
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'auto',
            html: true
        });
    }

    function cambiar2() {
        $("#fondo_container2").html("");
        var f = $("#tipo_fondo2").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + `Imagen de Fondo <span data-toggle="tooltip" title="Establece la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='file' class='form-control' name='fondo' required>` +
                `<label>Repetir <span data-toggle="tooltip" title="Establece si la imagen se repite en el fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>                
                <select class='form-control' name='repetir' required><option value='repeat'>SI</option><option value='no-repeat'>NO</option></select>`+
                `<label>Orientación Imagen <span data-toggle="tooltip" title="Establece la orientacion de la imagen de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>
                <select class='form-control' name='direccion' required><option value='center'>COLOCAR EN EL CENTRO</option><option value='left'>IZQUIERDA</option><option value='right'>DERECHA</option><option value='top'>ARRIBA</option></select>`;
        } else if (f == 'COLOR') {
            html = html + `Color de Fondo <span data-toggle="tooltip" title="Establece el color de fondo de la sección."> <i class="fa fa-question-circle"></i></span></label>            
            <input type='color' class='form-control' name='fondo' required>`;
        } else {
            html = "";
        }
        $("#fondo_container2").html(html);

        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'auto',
            html: true
        });
    }

    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'auto',
        html: true
    });

</script>
@endsection