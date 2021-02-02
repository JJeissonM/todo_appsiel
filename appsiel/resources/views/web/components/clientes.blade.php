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
        overflow-y: scroll;
        width: 70%;
        height: 100vh;
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
            <h4>.:: En ésta Sección: Clientes ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Clientes</h4>
            @if($clientes==null)
            <div class="add d-flex justify-content-end col-md-12">
                <a data-toggle="modal" data-target="#exampleModal" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Agregar Sección </a>
            </div>
            @else
            <div class="descripcion" style="text-align: center; margin-top: 20px;">
                <h5 class="titulo">{{$clientes->title}}</h5>
                <p>{{$clientes->descripcion}}</p>
                <a href="{{url('clientes/destroy/seccion').'/'.$clientes->id.$variables_url}}" class="btn btn-lg" title="Eliminar Seccion"><i class="fa fa-window-close"></i> ELIMINAR SECCIÓN</a>
            </div>
            <div class="col-md-12 add d-flex">
                <div class="col-md-6">
                    <a data-toggle="modal" data-target="#Modalcreate" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Agregar cliente </a>
                </div>
                <div class="col-md-6 justify-content-end">
                    <a data-toggle="modal" data-target="#Modaledit" class="btn btn-primary waves-effect btn-block btn-sm" style="color: white; font-weight: bold;"> Editar Sección </a>
                </div>
            </div>
            <div class="col-md-12">
                @foreach($clientes->clienteitems as $item)
                <div class="contenido">
                    <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                        <div class="pull-left">
                            <img src="{{asset($item->logo)}}">
                        </div>
                    </div>
                    <div class="descripcion">
                        <h5 class="titulo">{{$item->nombre}}</h5>
                        <p>{!! str_limit($item->enlace,15) !!}</p>
                    </div>
                    <a id="{{$item}}" onclick="editar(this.id)" data-toggle="modal" data-target="#Modaledit2" class="btn" title="Editar Ítem" style="color: #45aed6"><i class="fa fa-edit"></i></a>
                    <a href="{{url('clientes/destroy').'/'.$item->id.$variables_url}}" class="btn" title="Eliminar Cliente"><i class="fa fa-eraser"></i></a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        <div class="widgets" id="widgets" style="position: relative;">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($clientes != null)
            {!! Form::clientes($clientes)!!}
            @endif
        </div>
    </div>
</div>
@endsection


<div class="modal" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'clientes.storeSeccion','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Título de la Sección</label>
                        <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="title" type="text" placeholder="Nombre de la sección" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="descripcion" type="text" placeholder="Descripción del componente" required="required" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,null,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <label>¿El fondo es Imagen o Color?</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span>
                        <select type="select" class="form-control" id="tipo_fondo" required name="tipo_fondo" onchange="cambiar()">
                            <option value="">-- Seleccione una opción --</option>
                            <option value="IMAGEN">IMAGEN</option>
                            <option value="COLOR">COLOR</option>
                        </select>
                    </div>
                    <div class="form-group" id="fondo_container">
                    </div>
                    <div class="form-group">
                        <br /><br />
                        <a class="btn btn-danger" id="exampleModal" style="color: white" onclick="cerrar(this.id)">
                            Cancelar
                        </a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="Modalcreate" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'clientes.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="cliente_id" value="@if($clientes!=null){{$clientes->id}}@endif">
                    <div class="form-group">
                        <label>Nombre del Cliente</label>
                        <span data-toggle="tooltip" title="Establece el nombre del cliente a mostrar."> <i class="fa fa-question-circle"></i></span>
                        <input name="nombre" type="text" required placeholder="Nombre de la empresa o persona" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Logo (400 x 400 px)</label>
                        <span data-toggle="tooltip" title="Establece el logo del cliente a mostrar."> <i class="fa fa-question-circle"></i></span>
                        <input name="logo" type="file" placeholder="Agregar una imagen" required="required" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>URL del Sitio Web del Cliente (Opcional)</label>
                        <span data-toggle="tooltip" title="Establece la direccion del sitio web del cliente a mostrar."> <i class="fa fa-question-circle"></i></span>   
                        <input name="enlace" type="text" placeholder="Enlace del sitio web del cliente (opcional)" class="form-control">
                    </div>
                    <div class="form-group">
                        <br /><br />
                        <a class="btn btn-danger" id="Modalcreate" style="color: white" onclick="cerrar(this.id)">
                            Cancelar
                        </a>
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
                    @if($clientes!=null)
                    {!! Form::model($clientes,['route'=>['clientes.modificarSeccion',$clientes],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="cliente_id" value="@if($clientes!=null){{$clientes->id}}@endif">
                    <div class="form-group">
                        <label>Titulo</label>
                        <span data-toggle="tooltip" title="Establece el titulo o encabezado de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="title" type="text" placeholder="Titulo" value="{{$clientes->title}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <span data-toggle="tooltip" title="Establece la descripción de la sección."> <i class="fa fa-question-circle"></i></span>
                        <input name="descripcion" type="text" placeholder="Descripción" value="{{$clientes->descripcion}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="">Fuente Para el Componente</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                        @if($fonts!=null)
                        {!! Form::select('configuracionfuente_id',$fonts,$clientes->configuracionfuente_id,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                        @endif
                    </div>
                    <div class="form-group">
                        <label>¿El fondo es Imagen o Color?</label>
                        <span data-toggle="tooltip" title="Establece el tipo de fondo de la sección. De tipo Imagen o Color"> <i class="fa fa-question-circle"></i></span>
                        <select type="select" class="form-control" id="tipo_fondo2" name="tipo_fondo" onchange="cambiar2()">
                            @if($clientes->tipo_fondo=='IMAGEN')
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
                        @if($clientes->tipo_fondo=='IMAGEN')
                        <label>Imagen de Fondo</label>
                        <a target="_blank" href="{{asset($clientes->fondo)}}">Ver Actual</a><br>
                        <b>Repetir: {{$clientes->repetir}}</b><br>
                        <b>Orientación Imagen: {{$clientes->direccion}}</b>
                        @else
                        <label>Color de Fondo</label>
                        <div class="col-md-12" style="background-color: {{$clientes->fondo}}; width: 100%; height: 20px;"></div>
                        @endif
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="Modaledit" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    @endif
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="Modaledit2" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($clientes!=null)
                    {!! Form::open(['route'=>'clientes.modificar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <input type="hidden" name="cliente_id" id="cliente_id">
                    <div class="form-group">
                        <label>Nombre del Cliente</label>
                        <span data-toggle="tooltip" title="Establece el nombre del cliente a mostrar."> <i class="fa fa-question-circle"></i></span>
                        <input name="nombre" id="nombre" type="text" placeholder="Nombre de la empresa o persona" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Logo (400 x 400 px)</label>
                        <span data-toggle="tooltip" title="Establece el logo del cliente a mostrar."> <i class="fa fa-question-circle"></i></span>
                        <input name="logo" id="logo" type="file" placeholder="Agregar una imagen" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>URL del Sitio Web del Cliente (Opcional)</label>
                        <span data-toggle="tooltip" title="Establece la direccion del sitio web del cliente a mostrar."> <i class="fa fa-question-circle"></i></span>
                        <input id="enlace" name="enlace" type="text" placeholder="Enlace del sitio web del cliente (opcional)" class="form-control">
                    </div>
                    <div class="form-group">
                        <br /><br /><a class="btn btn-danger" id="Modaledit2" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                    </div>
                    @endif
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@section('script')
<script type="text/javascript">
    function cerrar(id) {
        $("#" + id).modal('hide');
        $("#" + id).removeClass('modal-open');
        $('.' + id).remove();
    }

    function editar(obj) {
        var item = JSON.parse(obj);
        $("#nombre").attr('value', item.nombre);
        $("#cliente_id").attr('value', item.id);
        $("#enlace").attr('value', item.enlace);
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
        placement: 'right',
        html: true
    });
</script>
@endsection