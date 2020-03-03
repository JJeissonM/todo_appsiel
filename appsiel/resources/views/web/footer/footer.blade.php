@extends('web.templates.main')

@section('style')
    <link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
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
            width: 70%;
            height: 100vh;
            overflow-y: scroll;
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

        .btn-link {
            cursor: pointer;
        }

        .panel {
            background-color: #fff;
            border: 1px solid transparent;
            border-radius: 4px;
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
            padding: 10px;
            margin-top: 5px;
            cursor: pointer;
            width: 100%;
        }

        .panel-title > a {
            padding: 10px;
            color: #000;
        }

        .panel-group .panel {
            margin-bottom: 0;
            border-radius: 4px;
        }

        .panel-default {
            border-color: #eee;
        }

        .article-ls {
            border: 1px solid;
            border-color: #3d6983;
            width: 100%;
            border-radius: 10px;
            -webkit-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
            -moz-box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
            box-shadow: 0px 0px 10px 5px rgba(61, 105, 131, 0.3);
        }

        .article-ls:focus {
            border-color: #9400d3;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
                <h4>.:: En ésta Sección: Footer ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Footer</h4>
                <div class="col-md-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Configuración de la Sección
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                 data-parent="#accordion">
                                <div class="card-body">
                                    <div class="col-md-12">
                                    @if($footer!=null)
                                        <!-- EDITAR -->
                                            {!! Form::model($footer,['route'=>['footer.update',$footer],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                                            <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                            <div class="form-group">
                                                <label>Background</label>
                                                <input type="color" class="form-control" required name="background"
                                                       style="background-color: #000;" value="{{$footer->background}}"
                                                       onchange="selectColor(event)">
                                            </div>
                                            <div class="form-group">
                                                <label>Color</label>
                                                <input type="color" class="form-control" required name="color"
                                                       style="background-color: white;" value="{{$footer->color}}"
                                                       onchange="selectColor(event)">
                                            </div>
                                            <div class="form-group">
                                                <label>Texto</label>
                                                <input type="text" class="form-control" name="texto" required
                                                       value="{{$footer->texto}}">
                                            </div>
                                            <div class="form-group">
                                                <label>Ubicación</label>
                                                <input type="text" class="form-control" name="ubicacion" required
                                                       value="{{$footer->ubicacion}}">
                                            </div>
                                            <div class="form-group">
                                                <label>Copyright</label>
                                                <input type="text" class="form-control" name="copyright" required
                                                       value="{{$footer->copyright}}">
                                            </div>
                                            <div class="form-group">
                                                {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                            </div>
                                            {!! Form::close() !!}
                                        @else
                                        <!-- CREAR -->
                                            {!! Form::open(['route'=>'footer.store','method'=>'POST','class'=>'form-horizontal'])!!}
                                            <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                            <div class="form-group">
                                                <label>Background</label>
                                                <input type="color" class="form-control" required name="background"
                                                       style="background-color: #000;" onchange="selectColor(event)">
                                            </div>
                                            <div class="form-group">
                                                <label>Color</label>
                                                <input type="color" class="form-control" required name="color"
                                                       style="background-color: white;" value="#fff"
                                                       onchange="selectColor(event)">
                                            </div>
                                            <div class="form-group">
                                                <label>Texto</label>
                                                <input type="text" class="form-control" name="texto" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Ubicación</label>
                                                <input type="text" class="form-control" name="ubicacion" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Copyright</label>
                                                <input type="text" class="form-control" name="copyright" required>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                            </div>
                                            {!! Form::close() !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#collapseTwo" aria-expanded="false"
                                            aria-controls="collapseTwo">
                                        Crear Nuevo Enlace
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                 data-parent="#accordion">
                                <div class="card-body">
                                    <div class="col-md-12">
                                    @if($footer != null)
                                        <!-- Formulario crear -->
                                            <div class="col-md-12" style="padding: 15px;">
                                                <button onclick="editor()" data-toggle="modal"
                                                        data-target="#newEnlace"
                                                        class="btn btn-primary waves-effect btn-block btn-sm">Abrir
                                                    Editor
                                                </button>
                                            </div>
                                        @else
                                            <p style="color: red;"><i class="fa fa-warning"></i> Antes de añadir las
                                                categorías debe configurar la sección.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse"
                                            data-target="#collapseThree" aria-expanded="false"
                                            aria-controls="collapseThree">
                                        Categorias en ésta Sección (Editar)
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                 data-parent="#accordion">
                                <div class="card-body">
                                    <div class="col-md-12" style="padding: 15px;">
                                        <p style="color: #3d6983;">Para editar haga clic en el ícono de edición <i
                                                    class="fa fa-edit"></i></p>
                                        @if($footer != null)
                                            @if(count($footer->categorias)>0)
                                                @foreach($footer->categorias as $a)
                                                    <div class="col-md-12 article-ls"
                                                         style="line-height: 20px; margin-bottom: 20px;">
                                                        <div class="media service-box"
                                                             style="margin: 10px !important; font-size: 14px;">
                                                            <div id="{{$a->id}}" data-toggle="modal"
                                                                 data-target="#exampleModal2" onclick="editar(this.id)"
                                                                 class="pull-left" data-toggle="tooltip"
                                                                 data-placement="top" title="Editar Sección">
                                                                <i style="cursor: pointer;" class="fa fa-edit"></i>
                                                            </div>
                                                            <div class="media-body">
                                                                <h6 style="font-size: 14px;"
                                                                    class="media-heading">{{$a->texto}}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p style="color: red;"><i class="fa fa-warning"></i> No hay secciones
                                                    publicadas...</p>
                                            @endif
                                        @else
                                            <p style="color: red;"><i class="fa fa-warning"></i> No hay seciiones
                                                publicadas...</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="widgets" id="widgets" style="display: block">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($footer != null)
                    <div>
                        {!! Form::footer($footer,$redes) !!}
                    </div>
                @else
                    <p style="color: red;"><i class="fa fa-warning"></i> La sección no ha sido configurada!</p>
                @endif
            </div>

            <div class="widgets" id="form-edit" style="display: none">
                <h4 class="column-title" style="padding: 10px;">Editar Seccion</h4>
                <div class="container-fluid">
                    <form action="" id="form-article-edit" method="POST" accept-charset="UTF-8">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="recipient-name" class="col-form-label">Título</label>
                                <input name="texto" type="text" class="form-control" id="tituloe">
                            </div>
                        </div>
                    </form>
                    <div class="col-md-12 d-flex justify-content-end" style="margin-top: 20px;">
                        <button type="button" onclick="cancelar()" class="btn btn-secondary" style="margin-right: 10px;"
                                data-dismiss="modal">Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" style="margin-right: 10px;" onclick="submit2()">
                            Guardar
                        </button>
                    </div>
                    <div class="col-md-12 " style="margin-top: 20px;">
                        <div class="col-md-12 d-flex justify-content-between">
                            <h5 class="card-title">Items</h5>
                            <a href="" style="color: #0000FF;" data-toggle="modal"
                               data-target="#enlace">+ Agregar items</a>
                        </div>
                        <table class="table">
                            <thead>
                            <th>Texto</th>
                            <th>Acciones</th>
                            </thead>
                            <tbody id="enlaces">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="newEnlace" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Crear Sección</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        {!! Form::open(['route'=>'footerstoreCategoria','method'=>'POST','id'=>'form-article','class'=>'form-horizontal'])!!}
                        <input type="hidden" name="variables_url" value="{{$variables_url}}">
                        @if($footer != null)
                            <input type="hidden" name="footer_id" value="{{$footer->id}}">
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <label for="recipient-name" class="col-form-label">Título</label>
                                <input name="texto" type="text" class="form-control" id="recipient-name">
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submit()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="enlace" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Crear Enlace</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <form action="{{route('newEnlace')}}" id="form-enlace">
                            <input type="hidden" name="_token" value="{{csrf_token()}}">
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" id="categoria" name="categoria_id" value="">
                            <div class="form-group">
                                <label for="">Titulo</label>
                                <input type="text" id="texto" class="form-control" name="texto" required>
                            </div>

                            <div class="form-group">
                                <label>Icono</label>
                                <input data-toggle="modal" data-target="#exampleModal" name="icono" type="text"
                                       id="iconotxt" placeholder="Nombre del icono" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="">Enlace</label>
                                <input type="text" class="form-control" id="enlacetxt" name="enlace"
                                       placeholder="https://">
                            </div>

                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitEnlace(event)">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" id="exampleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
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


@endsection

@section('script')

    <script src="{{asset('assets/js/axios.min.js')}}"></script>
    <script src="{{asset('js/sweetAlert2.min.js')}}"></script>

    <script>

        function selectColor(event) {
            event.target.style.backgroundColor = event.target.value;
        }

        function submit() {
            $("#form-article").submit();
        }

        function submit2() {
            $("#form-article-edit").submit();
        }

        function editor() {
            CKEDITOR.replace('contenido', {
                height: 200,
                removeButtons: ''
            });
        }

        function editar(id) {

            const url = '{{url('')}}/' + 'footer/' + id + '/categorias';
            document.getElementById('widgets').style.display = 'none';
            document.getElementById('form-edit').style.display = 'block';

            axios.get(url)
                .then(function (response) {
                    const data = response.data;
                    llenarTabla(data.enlaces);
                    if (data.status == 'ok') {
                        $("#tituloe").val(data.categoria.texto);
                        $('#categoria').val(data.categoria.id);
                        $("#form-article-edit").remove('action');
                        $("#form-article-edit").attr('action', '{{url('footer/edit/categoria/')}}' + "/" + data.categoria.id);
                    } else {
                        Swal.fire(
                            'Error!',
                            data.message,
                            'danger'
                        )
                    }
                });

        }

        function cancelar() {
            document.getElementById('form-edit').style.display = 'none';
            document.getElementById('widgets').style.display = 'block';
        }

        function submitEnlace() {

            let data = $('#form-enlace').serialize();

            axios.post('{{url('footer/categoria/enlace')}}', data)
                .then(function (response) {
                    data = response.data;
                    if (data.status == 'ok') {
                        llenarTabla(data.enlaces);
                        $('#enlace').modal('hide');
                        $('#texto').val('');
                        $('#enlacetxt').val('');
                        $('#iconotxt').val('');
                        Swal.fire(
                            'Exito!',
                            data.message,
                            'success'
                        );
                    } else {
                        Swal.fire(
                            'Error!',
                            data.message,
                            'danger'
                        );
                    }
                });

        }

        function llenarTabla(enlaces) {

            let tbody = document.getElementById('enlaces');
            tbody.innerHTML = '';
            let html = '';
            enlaces.forEach(x => {
                html += `<tr>
                              <td>${x.icono == '' ? '':'<i class="fa fa-'+x.icono+'"'+'></i>'} ${x.texto}</td>
                              <td><a href="" onclick="eliminarEnlace(event,${x.id})" style="color:red" <i class="fa fa-trash-o"></i></a></td>
                         </tr>`;
            });
            tbody.innerHTML = html;

        }

        function eliminarEnlace(event,id){
           event.preventDefault();
           axios.get('{{url('footer/eliminar/enlace')}}'+'/'+id)
               .then(function(response) {
                   const data = response.data;
                   if(data.status == 'ok'){

                      llenarTabla(data.enlaces);
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

    </script>
@endsection
