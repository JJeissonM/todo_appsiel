@extends('web.templates.main')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/tagsinput.css')}}">
@endsection

@section('content')

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between ">
                ITEMS
            </div>
            <div class="card-body">
              <form method="post" action="{{route('itemUpdate',$menu->id).$variables_url}}" ENCTYPE="multipart/form-data">
                  {!! csrf_field() !!}
                    <input type="hidden" id="parent" name="parent_id" value="{{$menu->parent_id}}">
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formGroupExampleInput">Nombre</label>
                                <span data-toggle="tooltip" title="Establece el nombre del elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" required class="form-control" id="formGroupExampleInput" placeholder="" name="titulo" value="{{$menu->titulo}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formGroupExampleInput">Descripción</label>
                                <span data-toggle="tooltip" title="Establece la desripción del elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" required class="form-control" id="formGroupExampleInput" placeholder="" name="descripcion" value="{{$menu->descripcion}}">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="formGroupExampleInput" for="orden">Orden</label>
                                <span data-toggle="tooltip" title="Establece la poscición del elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" class="form-control" id="orden" name="orden" value="{{$menu->orden}}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Icono(Opcional)</label>
                                <span data-toggle="tooltip" title="Establece un icono del elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input data-toggle="modal" value="{{$menu->icono}}" data-target="#exampleModal" name="icono" type="text" id="iconotxt" placeholder="Nombre del icono" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <h5>Enlazar a</h5>
                            <input type="hidden" id="tipo_enlace" name="tipo_enlace" value="url">
                            <nav>
                                <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                                    <a class="nav-item nav-link " id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true" onclick="select('pagina')">Página<span data-toggle="tooltip" title="Establece un enlace del elemento a una pagina."> <i class="fa fa-question-circle"></i></span></a>
                                    <a class="nav-item nav-link active" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false" onclick="select('url')">URL del sitio web <span data-toggle="tooltip" title="Establece un enlace del elemento a una pagina web externa."> <i class="fa fa-question-circle"></i></span></a>
                                </div>
                            </nav>
                            <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                                <div class="tab-pane fade " id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
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
                                        <span data-toggle="tooltip" title="Establece la pagina a la cual quieres enlazar el elemento."> <i class="fa fa-question-circle"></i></span>
                                        <select class="form-control" id="secciones" name="seccion">
                                            <option value="">Principio de la Página</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                    <div class="form-group">
                                        <label for="formGroupExampleInput">URL de sitio web (se abre en una pestaña nueva)</label>
                                        <input type="text" class="form-control"  placeholder="https://" name="url" value="{{$menu->enlace}}">
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

                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <!-- Button trigger modal -->
                        SUBITEMS
                    </div>
                    <div class="card-body">
                        <table class="table table-responsive" style="margin-top: 20px;">
                            <thead>
                            <th>Nombre</th>
                            <th>Enlace</th>
                            <th>Acciones</th>
                            </thead>
                            <tbody>
                            @foreach($menu->subMenus() as $item)
                                <tr>
                                    <td>{{$item->titulo}}</td>
                                    <td><a href="{{$item->enlace}}">{{$item->enlace}}</a></td>
                                    <td>
                                        <a href="{{route('menuItem.edit',$item->id).$variables_url}}" title="Editar Item" style="color: white;" class="btn  btn-sm bg-warning "><i class="fa fa-edit"></i></a>
                                        <a href="{{url('item/delete/'.$item->id).$variables_url}}" class="btn btn-danger btn-sm" title="Eliminar Item"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
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
                        $html +=`<option value="${item.widget_id}">${item.seccion}</option>`;
                    });
                    tbody.innerHTML = $html;
                });

        }

        function select(opcion) {
            let tipo = document.getElementById('tipo_enlace');
            tipo.value = opcion;
        }
        $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
    </script>

@endsection
