@extends('web.templates.main')

@section('style')
    <style>
        .card-body {
            padding: 0 !important;
            overflow-y: hidden;
        }
        #wrapper {
            overflow-y: scroll;
            height: 558px;
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
        .widgets img {
            width: 100%;
            object-fit: cover;
            height: 72.5vh;
            max-width: 100%;
        }
        .widgets .card-body {
            position: relative;
        }
        .activo{

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
            color:black;
            font-size: 16px;
        }

        .add {
            margin-top : 20px;
        }

        .add a {
            color:#1c85c4;
        }

    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap" >
            <div id="wrapper">
                {!! Form::open(['route' => 'slider.store','method'=>'POST','files'=>'true','style' => 'margin:10px;']) !!}
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <div class="form-group">
                    <label for="">Titulo</label>
                    <input type="text" class="form-control" placeholder="" name="titulo" required>
                </div>
                <div class="form-group">
                    <label for="">Descripción</label>
                    <textarea name="descripcion" id="" cols="30" rows="10" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label for="">Imagen</label>
                    <input type="file" class="form-control" name="imagen" required>
                </div>

                <div class="col-md-12">
                    <h5>Enlazar a</h5>
                    <input type="hidden" id="tipo_enlace" name="tipo_enlace" value="pagina">
                    <div class="form-group">
                        <label for="">Titulo del Enlace</label>
                        <input type="text" class="form-control" name="button" required>
                    </div>
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
                    <div class="buttons d-flex justify-content-end">
                        <button type="submit" class="btn btn-info mx-1">Guardar</button>
                        <a href="{{url('seccion/'.$widget).$variables_url}}" class="btn btn-danger mx-1">Cancelar</a>
                        <button type="reset" class="btn btn-warning mx-1">limpiar</button>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
            <div class="widgets" id="widgets">
                {!! Form::slider("") !!}
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

    </script>
@endsection
