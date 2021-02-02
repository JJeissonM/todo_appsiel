@extends('web.templates.main')

@section('style')
    <style>
        .card-body {
            padding: 0 !important;
            overflow-y: hidden;
        }

        #wrapper {
            overflow-y: scroll;
            width: 30%;
            margin-right: 0;
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


    </style>

@endsection

@section('content')<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Servicios ::.</h4>
        </div>
    </div>
</div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Editar Servicios</h4>
                <div class="col-md-12">
                    {!! Form::model($item,['route'=>['servicios.editar',$item],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="disposicion" value="{{$servicio->disposicion}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <span data-toggle="tooltip" title="Establece el titulo del Servicio."> <i class="fa fa-question-circle"></i></span>
                        <input name="titulo" type="text" placeholder="Titulo" value="{{$item->titulo}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <span data-toggle="tooltip" title="Establece la descripcion del Servicio."> <i class="fa fa-question-circle"></i></span>
                        <textarea name="descripcion" class="form-control contenido" rows="5">{{$item->descripcion}}</textarea>
                    </div>
                    <div class="form-group">
                        <label>URL (Solo si desea redirigir botón ver más)</label>
                        <span data-toggle="tooltip" title="Establece la URL a redirigir (Opcional)."> <i class="fa fa-question-circle"></i></span>
                        <input type="text" name="url" value="{{$item->url}}" class="form-control">
                    </div>
                    @if($servicio->disposicion=='ICONO')
                    <div class="form-group">
                        <label>Icono</label>
                        <span data-toggle="tooltip" title="Establece el icono del Servicio."> <i class="fa fa-question-circle"></i></span>
                        <input data-toggle="modal" data-target="#exampleModal" name="icono" value="{{$item->icono}}" type="text" id="iconotxt"
                               placeholder="Nombre del icono" class="form-control">
                    </div>
                    @else
                    <div class="form-group">
                        <label>Imáagen (410x291 px, bordes superiores redondeados)</label>
                        <span data-toggle="tooltip" title="Establece la imagen del Servicio."> <i class="fa fa-question-circle"></i></span>
                        <label>Actual (<a target="_blank" href="{{asset($item->icono)}}">Ver Imágen</a>)</label>
                        <input name="icono" type="file" placeholder="Archivo de Imagen" class="form-control">
                    </div>
                    @endif
                    <div class="form-group">
                        <br/><br/><a href="{{url('seccion/'.$widget).$variables_url}}"
                                     class="btn btn-danger">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="widgets" id="widgets">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($item->servicio != null)
                    {!! Form::servicios($item->servicio)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Icono</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::iconos($iconos) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@section('script')
    <script type="text/javascript">
        $(function () {
            $('#iconos').load('web/icons/view.blade.php');
        })
        $('.contenido').on( 'focus', function(){

            original_name = $(this).attr('name');

            $(this).attr('name','contenido');

            CKEDITOR.replace('contenido', {
                height: 200,
                // By default, some basic text styles buttons are removed in the Standard preset.
                // The code below resets the default config.removeButtons setting.
                removeButtons: ''
            });

        });

        $('.contenido').on( 'blur', function(){

            $(this).attr('name', original_name);

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
            placement: 'right',
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
            placement: 'right',
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
