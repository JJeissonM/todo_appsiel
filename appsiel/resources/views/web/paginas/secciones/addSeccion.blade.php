@extends('web.templates.main')

@section('style')
    <style>
        .card-body {
            padding: 0 !important;
            height: 72.5vh;
            overflow-y: hidden;
        }

        #wrapper {
            overflow-y: scroll;
            width: 30%;
            height: 70vh;
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

        .descripcion {
            position: absolute;
            padding: 10px;
            width: 100%;
            bottom: 0;
            background-color: #3d6983;
        }

        .descripcion p {
            color: white;
        }

        .activo {

        }
    </style>
@endsection

@section('content')

    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <ul class="list-group">
                    <?php $primero = true?>
                    @foreach($secciones as $seccion)
                        @if($primero)
                            <li class="list-group-item activo"
                                onclick="selectSeccion({{$seccion->id}})">{{$seccion->nombre}}</li>
                            <?php $primero = false; ?>
                        @else
                            <li class="list-group-item"
                                onclick="selectSeccion({{$seccion->id}})">{{$seccion->nombre}}</li>
                        @endif

                    @endforeach
                </ul>
            </div>
            <div class="widgets" id="widgets">
                @foreach($secciones as $seccion)
                    <div class="card" id="seccion_{{$seccion->id}}">
                        <div class="card-body">
                            <img src="{{asset($seccion->preview)}}" alt="...">
                            <div class="descripcion ">
                                <form action="{{url('pagina/nuevaSeccion').$variables_url}}" method="post"
                                      class="d-flex justify-content-between">
                                    <p>{{$seccion->descripcion}}</p>
                                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                                    <input type="hidden" name="seccion_id" value="{{$seccion->id}}">
                                    <<input type="hidden" name="pagina_id" value="{{$pagina}}">
                                    <button class="btn btn-info">Añadir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function selectSeccion(id) {
            let widgets = document.getElementById('widgets');
            const secciones = widgets.children;
            for (let i = 0; i < secciones.length; i++) {
                let item = secciones[i].getAttribute('id');
                document.getElementById(item).style.display = 'none';
            }
            let seccion = document.getElementById('seccion_' + id);
            seccion.style.display = 'block';
        }

    </script>
@endsection