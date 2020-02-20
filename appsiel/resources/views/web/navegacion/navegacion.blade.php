@extends('web.templates.main')

@section('content')

    <div class="title" style="margin: 0 0 20px 20px;">
        <h1>Vista Previa del Menú de Navegación</h1>
    </div>

    {{Form::navegacion($nav)}}

    <main style="margin-top: 150px">
        <section id="">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <!-- Button trigger modal -->
                        <button id="newItem" onclick="newItem(event)" type="button" class="btn btn-primary" data-toggle="modal"
                                data-target="#exampleModalCenter">
                            Nuevo Item
                        </button>

                    </div>
                    <div class="card-body">
                        <table class="table table-responsive" style="margin-top: 20px;">
                            <thead>
                            <th>Nombre</th>
                            <th>Enlace</th>
                            <th>Acciones</th>
                            </thead>
                            <tbody>
                                @foreach($nav->menus as $item)
                                    @if($item->parent_id == 0)
                                        <tr>
                                            <td>{{$item->titulo}}</td>
                                            <td><a href="{{$item->enlace}}">{{$item->enlace}}</a></td>
                                            <td>
                                                <a href="" class="btn btn-info btn-sm" title="Add subItem" onclick="addSubItem(event,{{$item->id}})"><i class="fa fa-plus-circle"></i></a>
                                                <a href="{{route('menuItem.edit',$item->id).$variables_url}}" title="Editar Item" style="color: white;" class="btn  btn-sm bg-warning "><i class="fa fa-edit"></i></a>
                                                <a href="{{url('item/delete/'.$item->id).$variables_url}}" class="btn btn-danger btn-sm" title="Eliminar Item"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modales -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Creando Nuevo Item para el menú de
                        navegación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{route('navegacion.store').$variables_url}}" ENCTYPE="multipart/form-data">
                        {!! csrf_field() !!}
                        <input type="hidden" id="parent" name="parent_id" value="0">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="formGroupExampleInput">Nombre</label>
                                    <input type="text" required class="form-control" id="formGroupExampleInput" placeholder="" name="titulo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="formGroupExampleInput">Descripción</label>
                                    <input type="text" required class="form-control" id="formGroupExampleInput" placeholder="" name="descripcion">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="customFile" for="customFile">Icono(opcional)</label>
                                    <input type="file" class="form-control" id="" name="icono">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h5>Enlazar a</h5>
                                <input type="hidden" id="tipo_enlace" name="tipo_enlace" value="pagina">
                                <nav>
                                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                                        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true" onclick="select('pagina')">Página</a>
                                        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false" onclick="select('url')">URL del sitio web</a>
                                    </div>
                                </nav>
                                <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                                        <div class="form-group" style="display: inline-block; width: 40%;">
                                            <label for="">Página</label>
                                            <select class="form-control" id="paginas" onchange="buscarSecciones(event)" name="pagina">
                                                @foreach($paginas as $pagina)
                                                    <option value="{{$pagina->id}}">{{$pagina->titulo}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group" style="display: inline-block;width: 58%;">
                                            <label for="">Sección en una página</label>
                                            <select class="form-control" id="secciones" name="seccion">
                                                <option value="">Principio de la Página</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                        <div class="form-group">
                                            <label for="formGroupExampleInput">URL de sitio web (se abre en una pestaña nueva)</label>
                                            <input type="text" class="form-control"  placeholder="https://" name="url">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('script')

    <script src="{{asset('assets/js/axios.min.js')}}"></script>
    <script>

        $(function(){
            const select =  document.getElementById('paginas');
            rellenarSelect(select);
        });

        function buscarSecciones(event){
            let select = event.target;
            rellenarSelect(select);
        }
        
        function rellenarSelect(select){

            select = select.options[select.selectedIndex].value;
            const url = '{{url('')}}/'+'pagina/secciones/'+select;

            axios.get(url)
                .then(function (response) {
                    const data =  response.data;
                    let tbody = document.getElementById('secciones');
                    let secciones = data.secciones;
                    $html = `<option value="principio">Principio de la página</option>`;
                    secciones.forEach(function (item) {
                        console.log(item);
                        $html +=`<option value="${item.widget_id}">${item.seccion}</option>`;
                    });
                    tbody.innerHTML = $html;
                });
        }

        function select(opcion) {
            let tipo = document.getElementById('tipo_enlace');
            tipo.value = opcion;
        }

        function addSubItem(event,parent){
           event.preventDefault();
           $('#parent').val(parent);
           $('#exampleModalCenter').modal('show');
        }

        function newItem(event){
            event.preventDefault();
            $('#parent').val(0);
            $('#exampleModalCenter').modal('show');
        }
        
    </script>
@endsection