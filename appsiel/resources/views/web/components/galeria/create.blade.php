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
                <h4 class="column-title" style="padding: 10px;">Crear Álbum</h4>
                <div class="col-md-12">
                    {!! Form::open(['route'=>'galeria.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo <span data-toggle="tooltip" title="Establece el título de la sección."> <i class="fa fa-question-circle"></i></span></label>
                        <input name="titulo" type="text" placeholder="Titulo del Álbum" required="required"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción del Álbum <span data-toggle="tooltip" title="Establece la descripción del album."> <i class="fa fa-question-circle"></i></span></label>
                        {!! Form::textarea('descripcion',null,['class'=>'form-control col-md-12 col-xs-12']) !!}
                    </div>
                    <div class="form-group">
                        <label>Añadir Imagenes (Las imagenes deben ser de 600px de alto por 400px de ancho y tamaño max de 2MB) <span data-toggle="tooltip" title="Establece las imagenes del album."> <i class="fa fa-question-circle"></i></span></label>
                        <input name="imagen[]" multiple type="file" placeholder="Agregar una imagen" required="required"
                               class="form-control">
                    </div>
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
                @if($galeria != null)
                    {!! Form::galeria($galeria)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'right',
        html: true
    });

</script>

@endsection
