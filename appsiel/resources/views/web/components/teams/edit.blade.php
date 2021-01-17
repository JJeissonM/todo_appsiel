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
            <h4>.:: En ésta Sección: Team (Equipos de trabajo, tarjetas, etc) ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Editar Tarjeta</h4>
            <div class="col-md-12">
                {!! Form::model($item,['route'=>['teams.editar',$item],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <div class="form-group">
                    <label>Titulo</label>
                    <input name="title" type="text" value="{{$item->title}}" placeholder="Titulo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Color del Título</label>
                    <input type='color' value="{{$item->title_color}}" class='form-control' name='title_color' required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" class="form-control" rows="5" required>{{$item->description}}</textarea>
                </div>
                <div class="form-group">
                    <label>Más Detalles (Parte Posterior) Máx: 250 caracteres</label>
                    <textarea name="more_details" class="form-control contenido" required rows="5">{{$item->more_details}}</textarea>
                </div>
                <div class="form-group">
                    <label>Color del Texto</label>
                    <input type='color' value="{{$item->text_color}}" class='form-control' name='text_color' required>
                </div>
                <div class="form-group">
                    <label>Imagen (410x291 px)</label>
                    <label>Actual (<a target="_blank" href="{{asset($item->imagen)}}">Ver Imágen</a>)</label>
                    <input name="imagen" type="file" required placeholder="Archivo de Imagen" class="form-control">
                </div>
                <div class="form-group">
                    <label>Color del Fondo de la Tarjeta</label>
                    <input type='color' class='form-control' value="{{$item->background_color}}" name='background_color' required>
                </div>
                <div class="form-group">
                    <br /><br /><a href="{{url('seccion/'.$widget).$variables_url}}" class="btn btn-danger">Cancelar</a>
                    <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                    {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($team != null)
            {!! Form::team($team)!!}
            @endif
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    $(function() {

    })
    $('.contenido').on('focus', function() {

        original_name = $(this).attr('name');

        $(this).attr('name', 'contenido');

        CKEDITOR.replace('contenido', {
            height: 200,
            // By default, some basic text styles buttons are removed in the Standard preset.
            // The code below resets the default config.removeButtons setting.
            removeButtons: ''
        });

    });

    $('.contenido').on('blur', function() {

        $(this).attr('name', original_name);

    });
</script>

@endsection