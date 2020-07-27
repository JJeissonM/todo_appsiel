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
                        <a href="{{url('galeria/eliminar').'/'.$galeria->id.$variables_url}}" class="btn btn-lg"
                           title="Eliminar Seccion"><i class="fa fa-window-close"></i></a>
                    </div>
                    <div class="col-md-12 add d-flex">
                        <div class="col-md-6">
                            <a href="{{url('galeria/create').'/'.$widget.$variables_url}}"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Agregar Nuevo Album</a>
                        </div>
                        <div class="col-md-6 justify-content-end">
                            <a data-toggle="modal" data-target="#Modaledit"
                               class="btn btn-primary waves-effect btn-block btn-sm"
                               style="color: white; font-weight: bold;"> Editar Sección </a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        @foreach($galeria->albums as $album)
                            <div class="contenido">
                                <?php 
                                    $primera_foto = $album->fotos->first();
                                    $url_primera_foto = url('img/avatar.png');
                                    if( !is_null( $primera_foto ) )
                                    {
                                        $url_primera_foto = url( $primera_foto->nombre );
                                    }
                                ?>
                                <img src="{{ $url_primera_foto }}" alt="" class="imagen">
                                <div class="descripcion">
                                    <h5 class="titulo">{{$album->titulo}}</h5>
                                    <p>{{str_limit($album->descripcion,30)}}</p>
                                </div>
                                <a href="{{url('galeria/edit').'/'.$album->id.$variables_url}}" class="btn"
                                   title="Editar Álbum"><i class="fa fa-edit"></i></a>
                                <a id="{{$album->id}}" href="" onclick="eliminarSeccion(event,id)" class="btn"
                                   title="Eliminar Álbum"><i class="fa fa-eraser"></i></a>
                            </div>
                        @endforeach
                    </div>
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
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo" class="form-control">
                    </div>
                    <div class="form-group">
                        <br/><br/><a class="btn btn-danger" id="exampleModal" style="color: white"
                                     onclick="cerrar(this.id)">Cancelar</a>
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
                            <label>Titulo</label>
                            <input name="titulo" type="text" placeholder="Titulo" value="{{$galeria->titulo}}"
                                   class="form-control">
                        </div>
                        <div class="form-group">
                            <br/><br/><a class="btn btn-danger" id="Modaledit" style="color: white"
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
@section('script')

    <script src="{{asset('assets/js/axios.min.js')}}"></script>
    <script src="{{asset('js/sweetAlert2.min.js')}}"></script>

    <script type="text/javascript">
        function cerrar(id) {
            $("#" + id).modal('hide');
            $("#" + id).removeClass('modal-open');
            $('.' + id).remove();
        }

        function eliminarSeccion(event,id){
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
                    axios.get('{{url('galeria/destroy/album/')}}'+'/'+id)
                        .then(function(response) {
                            const data = response.data;
                            if(data.status == 'ok'){
                                Swal.fire(

                                    'Eliminado!',
                                    'Su archivo ha sido eliminado.',
                                    'success'
                                );

                            }else {
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

    </script>
@endsection
