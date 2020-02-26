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
                            <textarea name="mision" class="form-control" rows="5">{{$aboutus->mision}}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Visión</label>
                            <textarea name="vision" class="form-control" rows="5">{{$aboutus->vision}}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Valores</label>
                            <textarea name="valores" class="form-control" rows="5">{{$aboutus->valores}}</textarea>
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
                            <textarea name="mision" class="form-control" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Visión</label>
                            <textarea name="vision" class="form-control" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Valores</label>
                            <textarea name="valores" class="form-control" rows="5"></textarea>
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

@section('script')

@endsection
