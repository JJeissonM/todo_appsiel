@extends('web.templates.main')

@section('style')
    <style>
        .card-body {
            padding: 0 !important;
            overflow: hidden;
            width: 100%;
            height: 100%;
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

        .activo {

        }

        .contenido {
            display: flex;
            justify-content: space-between;
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
                <h4>.:: En ésta Sección: Servicios ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Servicios</h4>
                @if($servicios != null)
                    <div class="descripcion" style="text-align: center; margin-top: 20px;">
                        <h5 class="titulo">{{$servicios->titulo}}</h5>
                        <p>{{str_limit($servicios->descripcion,30)}}</p>
                        <a href="{{url('servicios/destroy').'/'.$servicios->id.$variables_url}}" class="btn btn-lg"
                           title="Eliminar Seccion"><i class="fa fa-window-close"></i></a>
                    </div>
                    <div class="col-md-12 add d-flex">
                        <div class="col-md-6">
                            <a href="{{url('servicios/create').'/'.$widget.$variables_url}}"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Agregar Servicios </a>
                        </div>
                        <div class="col-md-6 justify-content-end">
                            <a data-toggle="modal" data-target="#Modaledit"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Editar Sección </a>
                        </div>
                    </div>
                    @if(count($servicios->itemservicios)>0)
                        @foreach($servicios->itemservicios as $item)
                            <div class="col-md-12">
                                <div class="contenido">
                                    <div class="media service-box wow fadeInRight animated"
                                         style="visibility: visible; animation-name: fadeInRight;">
                                        <div class="pull-left">
                                            <i class="fa fa-{{$item->icono}}"></i>
                                        </div>
                                    </div>
                                    <div class="descripcion">
                                        <h5 class="titulo">{{$item->titulo}}</h5>
                                        <p>{{str_limit($item->descripcion,30)}}</p>
                                    </div>
                                    <a href="{{url('servicios/edit').'/'.$item->id.$variables_url}}" class="btn"
                                       title="Editar Servicio"><i class="fa fa-edit"></i></a>
                                    <a href="{{url('servicios/destroy/item').'/'.$item->id.$variables_url}}" class="btn"
                                       title="Eliminar Servicio"><i class="fa fa-eraser"></i></a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @else
                    <div class="add d-flex justify-content-end col-md-12">
                        <a data-toggle="modal" data-target="#exampleModal"
                           class="btn btn-primary waves-effect btn-block btn-sm"
                           style="color: white; font-weight: bold;"> Agregar Sección </a>
                    </div>
                @endif
            </div>
            <div class="widgets" id="widgets">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($servicios != null)
                    {!! Form::servicios($servicios)!!}
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
                    {!! Form::open(['route'=>'servicios.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
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
                        <br/><br/><button class="btn btn-danger" id="exampleModal" style="color: white" onclick="cerrar(this.id)">Cancelar</button>
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
                    @if($servicios != null)
                        {!! Form::model($servicios,['route'=>['servicios.updated',$servicios],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                        <input type="hidden" name="widget_id" value="{{$widget}}">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <input type="hidden" name="servicio" value="{{$servicios->id}}">
                        <div class="form-group">
                            <label>Titulo</label>
                            <input name="titulo" type="text" placeholder="Titulo" value="{{$servicios->titulo}}"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <input name="descripcion" type="text" placeholder="Descripción"
                                   value="{{$servicios->descripcion}}" class="form-control">
                        </div>
                        <div class="form-group">
                            <br/><br/><a class="btn btn-danger" id="Modaledit" style="color: white" onclick="cerrar(this.id)">Cancelar</a>
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
<script type="text/javascript">

    function cerrar(id) {
        $("#"+id).modal('hide');
        $("#"+id).removeClass('modal-open');
        $('.'+id).remove();
    }
</script>
@endsection
