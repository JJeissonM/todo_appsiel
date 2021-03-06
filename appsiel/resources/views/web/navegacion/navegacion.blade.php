@extends('web.templates.main')

@section('content')

<div class="title" style="margin: 0 0 20px 20px;">
    <h1>Vista Previa del Menú de Navegación</h1>
</div>
<div class="position-relative border" style="height: 100px;">
@if($nav != null)
{{Form::navegacion($nav)}}
@endif
</div>


<main style="margin-top: 50px">
    <section id="">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="column-title">Configuraciones Generales</h5>
                        </div>
                        <div class="card-body">

                            <?php

if (is_null($nav)) {
    $fondos['background_0'] = "#574696";
    $fondos['background_1'] = "#42A3DC"; 
    
    $logo['imagen_logo'] = 'generic-logo-pngpng-5f09af84834b6.png';
    $logo['altura_logo'] = 50;
    $logo['anchura_logo'] = 80;

    $clase_header = 'no-fixed';

    $textcolor = '#fff';

    $fontfamily = 'sans-serif';

    $alpha = '10';
}else{
    if($nav->background != ''){
        $fondos = json_decode($nav->background, true);
    }else{
        $fondos['background_0'] = "#574696";
        $fondos['background_1'] = "#42A3DC"; 
    }
    if($nav->logo != ''){
        $logo = json_decode($nav->logo, true);
    }else{
        $logo['imagen_logo'] = '';
        $logo['altura_logo'] = 50;
        $logo['anchura_logo'] = 80;
    }
    
    $textcolor = $nav->color;
    $alpha = $nav->alpha;

    if ($nav->fixed) {
        $clase_header = 'fixed-top';
    } else {
        $clase_header = 'no-fixed';
    }

    if(!is_null($nav->configuracionfuente)){
        $fontfamily = $nav->configuracionfuente->fuente->font;
    }else {
        $fontfamily = 'sans-serif';
    }


}

                            /*$fondos = json_decode( $nav->background, true);

                            if (is_null($fondos)) {
                                $fondos['background_0'] = $nav->background;
                                $fondos['background_1'] = $nav->background;
                            }

                            $logo = json_decode($nav->logo, true);

                            if (is_null($logo)) {
                                $logo['imagen_logo'] = $nav->logo;
                                $logo['altura_logo'] = 50;
                                $logo['anchura_logo'] = 80;
                            }*/


                            // FORMULARIO
                            if ($nav == null) {
                                echo Form::open(['url' => route('navegacion.storenav'), 'method' => 'POST', 'files' => 'true']);
                                $checked = '';
                            } else {
                                echo Form::open(['url' => route('navegacion.update', $nav->id), 'method' => 'put', 'files' => 'true']);

                                $checked = '';
                                if(!is_null($nav)){
                                    if ($nav->fixed == true) {
                                        $checked = 'checked';
                                    }
                                }
                            }
                            ?>

                            <div class="form-group">
                                <label for="">Color Fondo</label>
                                <span data-toggle="tooltip" title="Establece en color de fondo de la navegacion."> <i class="fa fa-question-circle"></i></span>                               
                                <input type="color" id="background" onchange="selectColor(event)" class="form-control" name="background[]" value="{{ $fondos['background_0'] }}" required>
                            </div>

                            <div class="form-group">
                                <label for="">Color Fondo 2</label>
                                <span data-toggle="tooltip" title="Establece el color que se muestra al pasar el mouse por los elementos de la navegación."> <i class="fa fa-question-circle"></i></span>                               
                                <input type="color" id="background2" onchange="selectColor(event)" class="form-control" name="background[]" value="{{ $fondos['background_1'] }}" required>
                            </div>

                            <div class="form-group">
                                <label for="">Color texto</label>
                                <span data-toggle="tooltip" title="Establece el color del texto de la navegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="color" id="color" onchange="selectColor(event)" class="form-control" name="color" value="{{ $textcolor }}" required>
                            </div>

                            <div class="form-group">
                                <label for="imagen_logo">Logo</label>
                                <span data-toggle="tooltip" title="Establece una imagen(logo) en la navegación."> <i class="fa fa-question-circle"></i></span> <br>
                                @if( $logo['imagen_logo'] != '' )
                                <img src="{{asset($logo['imagen_logo'])}}" height="150px" width="150px">
                                @endif
                                <input type="file" class="form-control" name="imagen_logo">
                            </div>

                            <div class="form-group">
                                <label for="altura_logo">Altura Logo (px)</label>
                                <span data-toggle="tooltip" title="Establece establece la altura en pixeles de la imagen(logo) de la navegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="number" min="50" max="80" class="form-control" name="altura_logo" value="{{$logo['altura_logo']}}">
                            </div>

                            <div class="form-group" style="display: none"
                                <label for="anchura_logo">Anchura Logo (px)</label>
                                <span data-toggle="tooltip" title="Establece establece la anchura en pixeles de la imagen(logo) de la navegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="number" min="50" max="80" class="form-control" name="anchura_logo" value="{{$logo['anchura_logo']}}">
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input class="" type="checkbox" name="fixed" {{$checked}}>
                                        Fixed<span data-toggle="tooltip" title="Establece la navegacion anclada siempre en la parte superior de la vista de la pagina."> <i class="fa fa-question-circle"></i></span></label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="anchura_logo">Grado de Transparencia (solo para fixed)</label>
                                <span data-toggle="tooltip" title="Establece el grado de tranparencia de la navegación, esta caracteristica solo funciona si esta activada la funcion fixed."> <i class="fa fa-question-circle"></i></span>
                                <input type="range" min="1" max="10" class="form-control" name="alpha" value="{{$alpha}}">
                            </div>

                            <div class="form-group">
                                <label for="">Fuente Para el Componente</label>
                                <span data-toggle="tooltip" title="Establece el tipo de fuente de la sección."> <i class="fa fa-question-circle"></i></span>
                                @if(is_null($nav))
                                {!! Form::select('configuracionfuente_id',$fonts,'',['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                                @elseif($fonts!= null )
                                {!! Form::select('configuracionfuente_id',$fonts,$nav->configuracionfuente_id,['class'=>'form-control select2','placeholder'=>'-- Seleccione una opción --','required','style'=>'width: 100%;']) !!}
                                @endif
                            </div>

                            <div class="col-md-12 d-flex justify-content-end">
                                <button class="btn btn-info">Guardar</button>
                            </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <!-- Button trigger modal -->
                            <button id="newItem" onclick="newItem(event)" type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
                                Nuevo Item
                            </button>

                        </div>
                        <div class="card-body">
                            <table class="table table-responsive" style="margin-top: 20px;">
                                <thead>
                                    <th>Orden</th>
                                    <th>Nombre</th>
                                    <th>Enlace</th>
                                    <th>Acciones</th>
                                </thead>
                                <tbody>
                                    @if(!is_null($nav))
                                    @foreach($nav->menus as $item)
                                    @if($item->parent_id == 0)
                                    <tr>
                                        <td>{{$item->orden}}</td>
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
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>


<!-- Modales -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                                <span data-toggle="tooltip" title="Establece el nombre del nuevo elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" required class="form-control" id="formGroupExampleInput" placeholder="" name="titulo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="formGroupExampleInput">Descripción</label>
                                <span data-toggle="tooltip" title="Establece la desripción del nuevo elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" required class="form-control" id="formGroupExampleInput2" placeholder="" name="descripcion">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="formGroupExampleInput" for="orden">Orden</label>
                                <span data-toggle="tooltip" title="Establece la poscición del nuevo elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input type="text" class="form-control" id="orden" name="orden">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Icono</label>
                                <span data-toggle="tooltip" title="Establece un icono del nuevo elemento de la nacegación."> <i class="fa fa-question-circle"></i></span>
                                <input data-toggle="modal" data-target="#exampleModal" name="icono" type="text" id="iconotxt" placeholder="Nombre del icono" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <h5>Enlazar a</h5>
                            <input type="hidden" id="tipo_enlace" name="tipo_enlace" value="pagina">
                            <nav>
                                <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                                    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true" onclick="select('pagina')">Página<span data-toggle="tooltip" title="Establece un enlace del elemento a una pagina."> <i class="fa fa-question-circle"></i></span>
                                    </a>
                                    <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false" onclick="select('url')">URL del sitio web <span data-toggle="tooltip" title="Establece un enlace del elemento a una pagina web externa."> <i class="fa fa-question-circle"></i></span></a>
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
                                        <span data-toggle="tooltip" title="Establece la pagina a la cual quieres enlazar el elemento."> <i class="fa fa-question-circle"></i></span>
                                        <select class="form-control" id="secciones" name="seccion">
                                            <option value="">Principio de la Página</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                    <div class="form-group">
                                        <label for="formGroupExampleInput">URL de sitio web (se abre en una pestaña
                                            nueva)</label>
                                        <input type="text" class="form-control" placeholder="https://" name="url">
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
    $(function() {
        const select = document.getElementById('paginas');
        rellenarSelect(select);
        const color = document.getElementById('color');
        color.style.backgroundColor = color.getAttribute('value');
        const background = document.getElementById('background');
        background.style.backgroundColor = background.getAttribute('value');
        const background2 = document.getElementById('background2');
        background2.style.backgroundColor = background2.getAttribute('value');
    });

    function newItem(event) {
        event.preventDefault();
        $('#parent').val(0);
        $('#exampleModalCenter').modal('show');
    }

    function selectColor(event) {
        event.target.style.backgroundColor = event.target.value;
    }

    function buscarSecciones(event) {
        let select = event.target;
        rellenarSelect(select);
    }

    function rellenarSelect(select) {

        select = select.options[select.selectedIndex].value;
        const url = "{{url('')}}/" + "pagina/secciones/" + select;

        axios.get(url)
            .then(function(response) {
                const data = response.data;
                let tbody = document.getElementById('secciones');
                let secciones = data.secciones;
                $html = `<option value="principio">Principio de la página</option>`;
                secciones.forEach(function(item) {
                    $html += `<option value="${item.widget_id}">${item.seccion}</option>`;
                });
                tbody.innerHTML = $html;
            });
    }

    function select(opcion) {
        let tipo = document.getElementById('tipo_enlace');
        tipo.value = opcion;
    }

    function addSubItem(event, parent) {
        event.preventDefault();
        $('#parent').val(parent);
        $('#exampleModalCenter').modal('show');
    }
    $('[data-toggle="tooltip"]').tooltip({
            animated: 'fade',
            placement: 'right',
            html: true
        });
</script>
@endsection