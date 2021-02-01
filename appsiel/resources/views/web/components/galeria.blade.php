@extends('web.templates.main')

@section('style')
<link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
<style>
    .card-body {
        padding: 0 !important;
        overflow-y: hidden;
    }

    #wrapper {
        overflow-y: scroll;
        width: 30%;
        height: 100vh;
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

    .widgets img {
        width: 100%;
        object-fit: cover;
        max-width: 100%;
    }

    .widgets .card-body {
        position: relative;
    }

    .activo {}

    .contenido {
        display: flex;
        padding: 5px;
        margin-top: 10px;
        border: 1px solid #3d6983;
        border-radius: 5px;
    }

    .contenido img {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }

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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Galeria ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Galeria</h4>
            @if($galeria != null)
            <div class="descripcion" style="text-align: center; margin-top: 20px;">
                <h5 class="titulo">{{$galeria->titulo}}</h5>
                <a href="{{url('galeria/eliminar').'/'.$galeria->id.$variables_url}}" class="btn btn-lg" title="Eliminar Seccion"><i class="fa fa-window-close"></i></a>
            </div>
            <div class="col-md-12 add d-flex">
                <div class="col-md-6">
                    <a href="{{url('galeria/create').'/'.$widget.$variables_url}}" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Agregar Nuevo Album</a>
                </div>
                <div class="col-md-6 justify-content-end">
                    <a data-toggle="modal" data-target="#Modaledit" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Editar Sección </a>
                </div>
            </div>
            <div class="col-md-12">
                @foreach($galeria->albums as $album)
                <div class="contenido">
                    <?php
                    $primera_foto = $album->fotos->first();
                    $url_primera_foto = url('img/avatar.png');
                    if (!is_null($primera_foto)) {
                        $url_primera_foto = url($primera_foto->nombre);
                    }
                    ?>
                    <img src="{{ $url_primera_foto }}" alt="" class="imagen">
                    <div class="descripcion">
                        <h5 class="titulo">{{$album->titulo}}</h5>
                        <p>{{str_limit($album->descripcion,30)}}</p>
                    </div>
                    <a href="{{url('galeria/edit').'/'.$album->id.$variables_url}}" class="btn" title="Editar Álbum"><i class="fa fa-edit"></i></a>
                    <a id="{{$album->id}}" href="" onclick="eliminarSeccion(event,id)" class="btn" title="Eliminar Álbum"><i class="fa fa-eraser"></i></a>
                </div>
                @endforeach
            </div>
            @else
            <div class="add d-flex justify-content-end col-md-12">
                <a data-toggle="modal" data-target="#exampleModal" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Agregar Sección </a>
            </div>
            @endif
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($galeria != null)
            {!! Form::galeria($galeria)!!}
            @endif
        </div>
    </div>
</div>
@endsection
<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Galeria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'galeria.guardar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo <span data-toggle="tooltip" title="Establece el título o encabezado de la sección."> <i class="fa fa-question-circle"></i></span></label>
                        <input name="titulo" type="text" placeholder="Titulo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span></label>                        
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <label>¿El fondo es Imagen o Color? <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span></label>                        
                        <select type="select" class="form-control" id="tipo_fondo" required name="tipo_fondo" onchange="cambiar()">
                            <option value="">-- Seleccione una opción --</option>
                            <option value="IMAGEN">IMAGEN</option>
                            <option value="COLOR">COLOR</option>
                        </select>
                    </div>
                    <div class="form-group" id="fondo_container">
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="exampleModal" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="Modaledit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($galeria != null)
                    {!! Form::model($galeria,['route'=>['galeria.modificar',$galeria],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span></label>
                        <input name="titulo" type="text" placeholder="Titulo" value="{{$galeria->titulo}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span></label>
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,$galeria->configuracionfuente_id,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <label>¿El fondo es Imagen o Color? <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span></label>
                        <select type="select" class="form-control" id="tipo_fondo2" name="tipo_fondo" onchange="cambiar2()">
                            @if($galeria->tipo_fondo=='IMAGEN')
                            <option value="">-- Seleccione una opción --</option>
                            <option selected value="IMAGEN">IMAGEN</option>
                            <option value="COLOR">COLOR</option>
                            @else
                            <option value="">-- Seleccione una opción --</option>
                            <option value="IMAGEN">IMAGEN</option>
                            <option selected value="COLOR">COLOR</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group" id="fondo_container2">
                        @if($galeria->tipo_fondo=='IMAGEN')
                        <label>Imagen de Fondo</label>
                        <a target="_blank" href="{{asset($galeria->fondo)}}">Ver Actual</a><br>
                        <b>Repetir: {{$galeria->repetir}}</b><br>
                        <b>Orientación Imagen: {{$galeria->direccion}}</b>
                        @else
                        <label>Color de Fondo</label>
                        <div class="col-md-12" style="background-color: {{$galeria->fondo}}; width: 100%; height: 20px;"></div>
                        @endif
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="Modaledit" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@section('script')

<script src="{{asset('assets/js/axios.min.js')}}"></script>
<script src="{{asset('js/sweetAlert2.min.js')}}"></script>

<script type="text/javascript">
    function cerrar(id) {
        $("#" + id).modal('hide');
        $("#" + id).removeClass('modal-open');
        $('.' + id).remove();
    }

    function eliminarSeccion(event, id) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, bórralo!'
        }).then((result) => {
            if (result.value) {
                axios.get("{{url('galeria/destroy/album/')}}" + "/" + id)
                    .then(function(response) {
                        const data = response.data;
                        if (data.status == 'ok') {
                            Swal.fire(

                                'Eliminado!',
                                'Su archivo ha sido eliminado.',
                                'success'
                            );

                        } else {
                            Swal.fire(
                                'Error!',
                                data.message,
                                'danger'
                            )
                        }

                    });
            }
        });
    }

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