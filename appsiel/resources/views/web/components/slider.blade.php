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
        .activo{

        }
    </style>
@endsection

@section('content')

    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap" >
            <div id="wrapper">
                <ul class="list-group">

                </ul>
            </div>
            <div class="widgets" id="widgets">

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function selectSeccion(id){
            let widgets = document.getElementById('widgets');
            const secciones = widgets.children;
            for(let i = 0; i < secciones.length;i++){
                let item = secciones[i].getAttribute('id');
                document.getElementById(item).style.display = 'none';
            }
            let seccion = document.getElementById('seccion_'+id);
            seccion.style.display = 'block';
        }
    </script>
@endsection
