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
        height: 558px;
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
            <h4>.:: En ésta Sección: Login ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Menú Login (Configurar/Editar)</h4>
            @if($login == null)
            <div class="add d-flex justify-content-end col-md-12">
                {!! Form::open(['route'=>'login.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <div class="form-group">
                    <label>Titulo</label>
                    <input name="titulo" type="text" placeholder="Titulo del encabezado" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Imagen Acompañamiento</label>
                    <input name="imagen" type="file" placeholder="Agregar una imagen" required="required" class="form-control">
                </div>
                <div class="form-group">
                    <label>Dibujar Borde Ondulado</label>
                    <select type="select" class="form-control" required name="ondas">
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>¿El fondo es Imagen o Color?</label>
                    <select type="select" class="form-control" required id="tipo_fondo" name="tipo_fondo" onchange="cambiar()">
                        <option value="">-- Seleccione una opción --</option>
                        <option value="IMAGEN">IMAGEN</option>
                        <option value="COLOR">COLOR</option>
                    </select>
                </div>
                <div class="form-group" id="fondo_container">

                </div>
                <div class="form-group">
                    <label>Ruta</label>
                    <input name="ruta" required type="text" placeholder="Ruta de enlace al login" class="form-control">
                </div>
                <div class="form-group">
                    {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect']) !!}
                </div>
                {!! Form::close() !!}
            </div>
            @else
            <div class="add d-flex justify-content-end col-md-12">
                {!! Form::model($login,['route'=>['login.updated',$login->id],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <input type="hidden" name="login_id" id="login_id" {{$login->id}}>
                <div class="form-group">
                    <label>Titulo</label>
                    <input name="titulo" type="text" value="{{$login->titulo}}" placeholder="Titulo del encabezado" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Imagen Acompañamiento <a target="_blank" href="{{asset($login->imagen)}}">Ver Actual</a></label>
                    <input name="imagen" type="file" placeholder="Agregar una imagen" class="form-control">
                </div>
                <div class="form-group">
                    <label>Dibujar Borde Ondulado</label>
                    <select type="select" class="form-control" required name="ondas">
                        @if($login->ondas=='SI')
                        <option selected value="SI">SI</option>
                        <option value="NO">NO</option>
                        @else
                        <option value="SI">SI</option>
                        <option selected value="NO">NO</option>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label>¿El fondo es Imagen o Color?</label>
                    <select type="select" class="form-control" id="tipo_fondo" name="tipo_fondo" onchange="cambiar()">
                        @if($login->tipo_fondo=='IMAGEN')
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
                <div class="form-group" id="fondo_container">
                    @if($login->tipo_fondo=='IMAGEN')
                    <label>Imagen de Fondo</label>
                    <a target="_blank" href="{{asset($login->fondo)}}">Ver Actual</a>
                    @else
                    <label>Color de Fondo</label>
                    <div class="col-md-12" style="background-color: {{$login->fondo}}; width: 100%; height: 20px;"></div>
                    @endif
                </div>
                <div class="form-group">
                    <label>Ruta</label>
                    <input name="ruta" value="{{$login->ruta}}" required type="text" id="ruta" placeholder="Ruta de enlace al login" class="form-control">
                </div>
                <div class="form-group">
                    {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect']) !!}
                </div>
                {!! Form::close() !!}
            </div>
            @endif
        </div>
        <div class="widgets" id="widgets" style="position: relative;">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($login != null)
            {!! Form::login($login)!!}
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    function cambiar() {
        $("#fondo_container").html("");
        var f = $("#tipo_fondo").val();
        var html = "<label>";
        if (f == 'IMAGEN') {
            html = html + "Imagen de Fondo</label><input type='file' class='form-control' name='fondo' required>";
        } else if (f == 'COLOR') {
            html = html + "Color de Fondo</label><input type='color' class='form-control' name='fondo' required>";
        } else {
            html = "";
        }
        $("#fondo_container").html(html);
    }
</script>
@endsection