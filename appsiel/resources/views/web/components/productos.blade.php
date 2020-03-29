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

        .contenido {
            display: flex;
            padding: 5px;
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
                <h4>.:: En ésta Sección: Productos ::.</h4>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Menú Productos</h4>
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
                                    @if($pedido!=null)
                                        <!-- EDITAR -->
                                            {!! Form::model($pedido,['route'=>['pedidosweb.update',$pedido],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                                            <input type="hidden" name="widget_id" value="{{$widget}}">
                                            <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                            <div class="form-group">
                                                <label>Título</label>
                                                <input type="text" class="form-control" value="{{$pedido->titulo}}"
                                                       required name="titulo">
                                            </div>
                                            <div class="form-group">
                                                <label>Descripción</label>
                                                <input type="text" class="form-control" value="{{$pedido->descripcion}}"
                                                       name="descripcion">
                                            </div>
                                            <div class="form-group">
                                                {!! Form::submit('Guardar',['class'=>'btn btn-primary waves-effect btn-block btn-sm']) !!}
                                            </div>
                                            {!! Form::close() !!}
                                        @else
                                        <!-- CREAR -->
                                            {!! Form::open(['route'=>'pedidosweb.store','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                                            <input type="hidden" name="widget_id" value="{{$widget}}">
                                            <input type="hidden" name="variables_url" value="{{$variables_url}}">
                                            <div class="form-group">
                                                <label>Título</label>
                                                <input type="text" class="form-control" required name="titulo">
                                            </div>
                                            <div class="form-group">
                                                <label>Descripción</label>
                                                <input type="text" class="form-control" name="descripcion">
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
                    </div>
                </div>
            </div>
            <div class="widgets" id="widgets">
                <h4 class="column-title" style="padding: 10px;">Configuraciones</h4>
                <div class="col-md-12">
                    <input type="hidden" id="tipo_enlace" name="tipo_enlace" value="pagina">
                    <nav>
                        <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab"
                               href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"
                               onclick="select('pagina')">General</a>
                            <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab"
                               href="#nav-profile" role="tab" aria-controls="nav-profile"
                               aria-selected="false">Productos</a>
                            <a class="nav-item nav-link" id="nav-inventario-tab" data-toggle="tab"
                               href="#nav-inventario" role="tab" aria-controls="nav-profile"
                               aria-selected="false">Inventario</a>
                            <a class="nav-item nav-link" id="nav-envio-tab" data-toggle="tab"
                               href="#nav-envio" role="tab" aria-controls="nav-profile"
                               aria-selected="false">Terminos y Condiciones</a>
                            <a class="nav-item nav-link" id="nav-correos-tab" data-toggle="tab"
                               href="#nav-correo" role="tab" aria-controls="nav-profile"
                               aria-selected="false">Correos Electronicos</a>
                        </div>
                    </nav>
                    <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                             aria-labelledby="nav-home-tab">
                            <div class="col-md-12">
                                <i>Aquí es donde esta situado tu negocio</i></br></br>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Dirección, linea 1</label></div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="direccion1" required="required"
                                               placeholder="Dirección del negocio">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Dirección, linea 2</label></div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="direccion2" required="required"
                                               placeholder="Dirección del negocio">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Pais</label></div>
                                    <div class="col-md-8">
                                        <select class="form-control" id="pais" name="pais">
                                            <option value="">--Selecciones una opción--</option>
                                            @foreach($paises as $pais)
                                                <option value="{{$pais->id}}">{{$pais->descripcion}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Ciudad</label></div>
                                    <div class="col-md-8">
                                        <select class="form-control" id="ciudad" name="ciudad">
                                            <option value="">--Selecione una opción--</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Codigo Postal</label></div>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control" name="codigo_postal"
                                               required="required" placeholder="Dirección del negocio">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-profile" role="tabpanel"
                             aria-labelledby="nav-profile-tab">
                            <div class="col-md-12">
                                <i>Configuraciones de medidas de los productos</i></br></br>
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <label>Comportamiendo de añadir al carrito</label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="comportamiento_carrito"
                                                   name="comportamiento_carrito">
                                            <label class="custom-control-label" for="customRadio1">Redirigir a la pagina
                                                del carrito tras añadir productos con exito.</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="comportamiento_carrito"
                                                   name="comportamiento_carrito" checked>
                                            <label class="custom-control-label" for="customRadio1">Activar botenes AJAX
                                                de añadir al carrito.</label>
                                        </div>
                                    </div>
                                </div>
                                <h6>Medidas</h6></br>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Unidad de Peso</label></div>
                                    <div class="col-md-8">
                                        <select class="form-control" id="unidad_peso" name="unidad_peso">
                                            <option value="lbs">lbs</option>
                                            <option value="kg">kg</option>
                                            <option value="g">g</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Unidad de Dimensiones</label></div>
                                    <div class="col-md-8">
                                        <select class="form-control" id="unidad_dimensiones" name="unidad_dimensiones">
                                            <option value="cm">cm</option>
                                            <option value="mts">mts</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-inventario" role="tabpanel"
                             aria-labelledby="nav-profile-tab">
                            <div class="col-md-12">
                                <i>Configuraciones de inventario</i></br></br>
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <label>Avisos</label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="aviso_poca_exitencia" name="aviso_poca_exitencia"
                                                   checked>
                                            <label class="custom-control-label" for="customCheck1">Activar aviso de
                                                pocas existencias</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="aviso_inventario_agotado"
                                                   name="aviso_inventario_agotado" checked>
                                            <label class="custom-control-label" for="customCheck2">Activar aviso de
                                                inventario agotado</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Destinatario de los avisos</label></div>
                                    <div class="col-md-8">
                                        <input type="email" class="form-control" name="email_destinatario"
                                               placeholder="Correo de donde se enviaran los avisos" required="required">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Umbral de pocas existencias</label></div>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control" min="0" max="500"
                                               name="umbral_existencia"
                                               placeholder="Cantidad minima para enviar aviso de poca existencia"
                                               required="required">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Umbral de inventario agotado</label></div>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control" min="0" max="500"
                                               name="umbral_existencia" value="0"
                                               placeholder="Cantidad minima para enviar aviso de inventario agotado"
                                               required="required">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Visibilidad de inventario agotado</label></div>
                                    <div class="col-md-8">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="visibilidad_inv_agotado"
                                                   name="visibilidad_inv_agotado">
                                            <label class="custom-control-label" for="customCheck3">Ocultar en el
                                                catalogo los articulos agotados</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"><label>Formato de visualización del inventario</label></div>
                                    <div class="col-md-8">
                                        <select class="form-control" id="mostrar_inventario" name="mostrar_inventario">
                                            <option value="SIEMPRE">Siempre mostrar la cantidad restante del inventario
                                                p.ej: -12 existencias-
                                            </option>
                                            <option value="POCA">Solo mostrar la cantidad restante del inventario cuando
                                                sea baja p.ej. -solo quedan 2 existencias-
                                            </option>
                                            <option value="NUNCA">Nunca mostrar la cantidad restnate del inventario
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-envio" role="tabpanel"
                             aria-labelledby="nav-profile-tab">
                            <div class="form-group">
                                <label for="formGroupExampleInput">envio</label>
                                <input type="text" class="form-control" placeholder="https://" name="url">
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-correo" role="tabpanel"
                             aria-labelledby="nav-profile-tab">
                            <div class="col-md-12">
                                <table id="myTable" class="table table-responsive table-striped">
                                    <thead>
                                    <th>Correo Electronico</th>
                                    <th>Destinatario</th>
                                    <th>Acciones</th>
                                    </thead>
                                    <tbody id="paginas">
                                    @if(count($correo->itemcorreos)>0)
                                        @foreach($correo->itemcorreos as $c)
                                            <tr>
                                                <td>@if($c->activo == 'SI')<input type="checkbox" name="activo"
                                                                                  checked>@else <input type="checkbox"
                                                                                                       name="activo">@endif{{$c->correo}}
                                                </td>
                                                <td>{{$c->destinatario}}</td>
                                                <td>
                                                    <btn onclick="gestionar(this.id)" id="{{$c}}" class="btn"
                                                         title="Gestionar" data-toggle="modal"
                                                         data-target="#gestioncorreo">Gestionar
                                                    </btn>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                                <br>
                                <h6></h6>
                            </div>
                        </div>
                    </div>
                </div>
                {{--            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>--}}
                {{--            @if($pedido != null)--}}
                {{--            {!! Form::productos($items,$pedido)!!}--}}
                {{--            @else--}}
                {{--            <p style="color: red;"> <i class="fa fa-warning"></i> La sección no ha sido configurada!</p>--}}
                {{--            @endif--}}
            </div>
        </div>
    </div>
    <!-- Modal  Gestion correo-->
    <div class="modal fade" id="gestioncorreo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Gestionar Correo</h5><br><br>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 id="titulo"></h6></br></br>
                    <div class="container-fluid" id="rta">
                        <div class="col-md-12">
                            {!! Form::open(['route'=>'correo.modificaritem','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                            <input type="hidden" name="itemcorreo" id="itemcorreo_id">
                            <input type="hidden" name="widget_id" value="{{$widget}}">
                            <input type="hidden" name="variables_url" value="{{$variables_url}}">
                            <div class="form-group row">
                                <div class="col-md-4"><label>Activar/Desactivar</label></div>
                                <div class="col-md-8">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="activo"
                                               name="activo">
                                        <label class="custom-control-label" for="customCheck3">Activar este aviso por correo electronico</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4"><label>Asunto</label></div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="asunto" id="asunto">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4"><label>Encabezado</label></div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" name="encabezado" id="encabezado">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Contenido</label>
                                <textarea type="text" class="form-control area" name="contenido" id="contenido"
                                          rows="5"></textarea>
                            </div>
                            <div class="form-group">
                                <br/><br/><a class="btn btn-danger" id="gestioncorreo" style="color: white"
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
    </div>
@endsection

@section('script')

    <script type="text/javascript">
        $(function () {

        });

        function cerrar(id) {
            $("#" + id).modal('hide');
            $("#" + id).removeClass('modal-open');
            $('.' + id).remove();
        }

        function gestionar(id) {
            var item = JSON.parse(id);
            $("#itemcorreo_id").attr('value',item.id);
            $("#titulo").html(item.correo);
            $("#asunto").attr('value', item.asunto);
            $("#asunto").val(item.asunto);
            $("#encabezado").attr('value', item.encabezado);
            $("#encabezado").val(item.encabezado);
            $("#contenido").attr('value', item.contenido);
            $("#contenido").val(item.contenido);
        }

        function submit() {
            $("#form-article").submit();
        }

        function eliminar(id) {
            $("#form-archivo").submit();
        }

        $('.area').on('focus', function () {

            original_name = $(this).attr('name');

            $(this).attr('name', 'area');

            CKEDITOR.replace('area', {
                height: 200,
                // By default, some basic text styles buttons are removed in the Standard preset.
                // The code below resets the default config.removeButtons setting.
                removeButtons: ''
            });

        });

        $('.area').on('blur', function () {

            $(this).attr('name', original_name);

        });
    </script>

@endsection