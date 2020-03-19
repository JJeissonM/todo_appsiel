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

        .activo {

        }

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
                <h4>.:: En ésta Sección: Testimoniales ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Testimoniales</h4>
                @if($testimonial == null)
                    <div class="add d-flex justify-content-end col-md-12">
                        <a data-toggle="modal" data-target="#crearseccion"
                           class="btn btn-primary waves-effect btn-block btn-sm"
                           style="color: white; font-weight: bold;"> Agregar Sección</a>
                    </div>
                @else
                    <div class="descripcion" style="text-align: center; margin-top: 20px;">
                        <h5 class="titulo">{{$testimonial->titulo}}</h5>
                        <p>{{str_limit($testimonial->descripcion,30)}}</p>
                        <a href="{{url('testimonial/destroy').'/'.$testimonial->id.$variables_url}}"
                           class="btn btn-lg"
                           title="Eliminar Seccion"><i class="fa fa-window-close"></i></a>
                    </div>
                    <div class="col-md-12 add d-flex">
                        <div class="col-md-6">
                            <a data-toggle="modal" data-target="#Crearpregunta"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Agregar Testimonio</a>
                        </div>
                        <div class="col-md-6 justify-content-end">
                            <a data-toggle="modal" data-target="#edit2"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Editar Sección </a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        @if(count($testimonial->itemtestimonials)>0)
                            @foreach($testimonial->itemtestimonials as $item)
                                <div class="contenido">
                                    <img src="{{url($item->foto)}}" alt="" class="imagen">
                                    <div class="descripcion">
                                        <h5 class="titulo">{{$item->nombre}}</h5>
                                        <p>{{str_limit($item->testimonio,30)}}</p>
                                    </div>
                                    <a id="{{$item}}" onclick="editar(this.id)" data-toggle="modal"
                                       data-target="#Modaledit"
                                       class="btn"
                                       title="Editar Testimonio" style="color: #45aed6"><i
                                                class="fa fa-edit"></i></a>
                                    <a href="{{url('testimonial/eliminar/itemtestimonial').'/'.$item->id.$variables_url}}"
                                       class="btn"
                                       title="Eliminar Testimonio"><i class="fa fa-eraser"></i></a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>
            <div class="widgets" id="widgets" style="position: relative;">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($testimonial != null)
                    {!! Form::testimoniales($testimonial)!!}
                @endif
            </div>
        </div>
    </div>
@endsection

<div class="modal" id="crearseccion" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Sección</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::open(['route'=>'testimonial.guardar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo de la sección"
                               class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required="required"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Imagen de Fondo</label>
                        <input type="file" name="imagen_fondo" class="form-control" rows="3">
                    </div>
                    <div class="form-group">
                        <br/><br/>
                        <a class="btn btn-danger" id="crearseccion" style="color: white" onclick="cerrar(this.id)">
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

<div class="modal" id="edit2" tabindex="-1" role="dialog">
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
                    @if($testimonial != null)
                        {!! Form::model($testimonial,['route'=>['testimonial.updated',$testimonial],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <div class="form-group">
                            <label>Titulo</label>
                            <input name="titulo" type="text" placeholder="Titulo" value="{{$testimonial->titulo}}"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <input name="descripcion" type="text" placeholder="Titulo"
                                   value="{{$testimonial->descripcion}}"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <br/><br/><a class="btn btn-danger" id="edit2" style="color: white"
                                         onclick="cerrar(this.id)">Cancelar</a>
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

<div class="modal" id="Crearpregunta" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Testimonio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($testimonial != null)
                        {!! Form::open(['route'=>'testimonial.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <input type="hidden" name="testimoniale_id" value="{{$testimonial->id}}">
                        <div class="form-row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input name="nombre" type="text" placeholder="Nombre de la pregunta"
                                           class="form-control" required="required">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cargo</label>
                                    <input name="cargo" type="text" placeholder="Cargo de la persona"
                                           class="form-control" required="required">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Foto</label>
                                    <input name="foto" type="file" placeholder="foto"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Testimonio</label>
                                    <textarea name="testimonio" class="form-control" rows="3"
                                              required="required"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <br/><br/>
                                <a class="btn btn-danger" id="Crearpregunta" style="color: white"
                                   onclick="cerrar(this.id)">
                                    Cancelar
                                </a>
                                <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                                {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="Modaledit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Pregunta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    @if($testimonial != null)
                        {!! Form::open(['route'=>'testimonial.modificar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <input type="hidden" name="itemtestimonial_id" id="itemtestimonial_id">
                        <div class="form-row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input name="nombre" id="nombre" type="text" placeholder="Nombre de la pregunta"
                                           class="form-control" required="required">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cargo</label>
                                    <input name="cargo" id="cargo" type="text" placeholder="Cargo de la persona"
                                           class="form-control" required="required">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Foto</label>
                                    <input name="foto" type="file" placeholder="foto"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Testimonio</label>
                                    <textarea name="testimonio" id="testimonio" class="form-control" rows="3"
                                              required="required"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <br/><br/><a class="btn btn-danger" id="Modaledit" style="color: white"
                                             onclick="cerrar(this.id)">Cancelar</a>
                                <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                                {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                            </div>
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
            $("#cargo").attr('value',item.cargo)
            $("#testimonio").val(item.testimonio);
            $("#testimonio").attr('value', item.testimonio);
            $("#itemtestimonial_id").attr('value', item.id);
        }
    </script>
@endsection
