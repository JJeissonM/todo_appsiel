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
                <h4>.:: En ésta Sección: About Us ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                @if($aboutus != null)
                    <h4 class="column-title" style="padding: 10px;">Editar About Us</h4>
                    <div class="col-md-12">
                        {!! Form::model($aboutus,['route'=>['aboutus.updated',$aboutus],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <div class="form-group">
                            <label>Titulo</label>
                            <input name="titulo" type="text" placeholder="Titulo" value="{{$aboutus->titulo}}"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <input name="descripcion" type="text" placeholder="Descripción"
                                   value="{{$aboutus->descripcion}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Misión</label>
                            <textarea name="mision" class="form-control contenido" rows="5">{{$aboutus->mision}}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Icono Misión</label>
                            <input data-toggle="modal" data-target="#exampleModal" name="mision_icono" value="{{$aboutus->mision_icono}}" type="text" id="iconotxt"
                                   placeholder="Nombre del icono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Visión</label>
                            <textarea name="vision" class="form-control contenido" rows="5">{{$aboutus->vision}}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Icono Visión</label>
                            <input data-toggle="modal" data-target="#exampleModal" name="vision_icono" value="{{$aboutus->vision_icono}}" type="text" id="iconotxt"
                                   placeholder="Nombre del icono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Valores</label>
                            <textarea name="valores" class="form-control contenido" rows="5">{{$aboutus->valores}}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Icono Valores</label>
                            <input data-toggle="modal" data-target="#exampleModal" name="valor_icono" value="{{$aboutus->valor_icono}}" type="text" id="iconotxt"
                                   placeholder="Nombre del icono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Reseña Historica</label>
                            <textarea name="resenia" class="form-control contenido" rows="5">{{$aboutus->resenia}}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Icono Reseña</label>
                            <input data-toggle="modal" data-target="#exampleModal" name="resenia_icono" value="{{$aboutus->resenia_icono}}" type="text" id="iconotxt"
                                   placeholder="Nombre del icono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Imagen</label>
                            <input name="imagen" type="file" placeholder="Agregar una imagen" class="form-control">
                        </div>
                        <div class="form-group">
                            <br/><br/><a href="{{url('seccion/'.$widget).$variables_url}}"
                                         class="btn btn-danger">Cancelar</a>
                            <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                            {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                @else
                    <h4 class="column-title" style="padding: 10px;">Crear About Us</h4>
                    <div class="col-md-12">
                        {!! Form::open(['route'=>'aboutus.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <div class="form-group">
                            <label>Titulo</label>
                            <input name="titulo" type="text" placeholder="Titulo" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <input name="descripcion" type="text" placeholder="Descripción" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Misión</label>
                            <textarea name="mision" class="form-control contenido" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Visión</label>
                            <textarea name="vision" class="form-control contenido" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Valores</label>
                            <textarea name="valores" class="form-control contenido" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Reseña</label>
                            <textarea name="resenia" class="form-control contenido" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Imagen</label>
                            <input name="imagen" type="file" placeholder="Agregar una imagen" required="required"
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
                @endif
            </div>
            <div class="widgets" id="widgets">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($aboutus != null)
                    {!! Form::aboutus($aboutus)!!}
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
        var original_name;


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

    </script>
@endsection
