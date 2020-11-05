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
            height: 558px;
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
                <h4>.:: En ésta Sección: Html personalizado ::.</h4>




            </div>
        </div>
    </div>
    <div class="card">
        <div class="body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <h4 class="column-title" style="padding: 10px;">Editar Html personalizado</h4>
                @if($registro != null)
                    {{ Form::model($registro, ['url' => 'custom_html/'.$registro->id, 'method' => 'PUT','id'=>'form_create','files' => true]) }}
                    <?php
                        $contenido = $registro->contenido;
                    ?>
                @else
                    {{ Form::open(['url'=>'custom_html','id'=>'form_create','files' => true]) }}
                    <?php
                        $contenido = '';
                    ?>
                @endif

                    <label for="imagen" class="control-label"> Imágen </label>
                    {{ Form::file( 'imagen', [ 'id' => 'imagen', 'accept' => 'jpg,png,gif' ] ) }}

                    <label class="form-label">Contenido</label>
                    <textarea name="contenido" class="form-control contenido" rows="15" required="required">{{ $contenido }}</textarea>

                    {{ Form::hidden('widget_id', $widget) }}
                    {{ Form::hidden('url_id',Input::get('id')) }}

                    <div class="form-group">
                        <br/><br/>
                        {{ Form::bsButtonsForm( 'paginas?id=' . Input::get('id') ) }}
                    </div>
                    
                {{ Form::close() }}
            </div>
            <div class="widgets" id="widgets" style="position: relative;">
                <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
                @if($registro != null)
                    {!! Form::custom_html( $registro, '')!!}
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function(){

            CKEDITOR.replace('contenido', {
                height: 200,
                removeButtons: ''
            });

            $('#bs_boton_guardar').on('click',function(event){
                event.preventDefault();

                // Desactivar el click del botón
                $( this ).off( event );

                $('#form_create').submit();
            });

        });
    </script>
@endsection
