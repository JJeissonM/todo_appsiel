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
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <div class="col-md-12">
                    {!! Form::model($item,['route'=>['servicios.editar',$item],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                    <input type="hidden" name="widget_id" value="{{$widget}}">
                    <input type="hidden" name="variables_url" value="{{$variables_url}}">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo" value="{{$item->titulo}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input name="descripcion" type="text" placeholder="Descripción" value="{{$item->descripcion}}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Icono</label>
                        <input data-toggle="modal" data-target="#exampleModal" name="icono" value="{{$item->icono}}" type="text" id="iconotxt"
                               placeholder="Nombre del icono" class="form-control">
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
    </script>
@endsection
