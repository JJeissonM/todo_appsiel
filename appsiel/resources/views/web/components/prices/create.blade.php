@extends('web.templates.main')

@section('style')
<style>
    .card-body {
        padding: 0 !important;
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
</style>

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Price (Planes de Precios) ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Crear Plan</h4>
            <div class="col-md-12">
                {!! Form::open(['route'=>'prices.guardar','method'=>'POST','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="price_id" value="{{$price->id}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <div class="form-group">
                    <label>URL Para el Botón</label>
                    <input name="url" type="text" placeholder="Url que abrirá el botón" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Precio</label>
                    <input name="precio" type="text" placeholder="Precio del plan, ejemplo: $50,000/mes" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Color del Texto (Ítems del plan)</label>
                    <input type='color' class='form-control' name='text_color' required>
                </div>
                <div class="form-group">
                    <label>Imagen de la Cabecera(400x200 px)</label>
                    <input name="imagen_cabecera" type="file" required placeholder="Archivo de Imagen" class="form-control">
                </div>
                <div class="form-group">
                    <label>Color del Botón 1</label>
                    <input type='color' class='form-control' name='button_color' required>
                </div>
                <div class="form-group">
                    <label>Color del Botón 2</label>
                    <input type='color' class='form-control' name='button2_color' required>
                </div>
                <div class="form-group">
                    <label>Color del Fondo del Plan</label>
                    <input type='color' class='form-control' name='background_color' required>
                </div>
                <div class="table-responsive col-md-12" id="table_content">
                    <h4>Ítems del Plan</h4>
                    <a onclick="addRow()" style="color: #fff;" class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> Agregar Ítem</a>
                    <table id="items" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Ícono</th>
                                <th>Ítem</th>
                                <th>Quitar</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="form-group">
                    <br /><br /><a href="{{url('seccion/'.$widget).$variables_url}}" class="btn btn-danger">Cancelar</a>
                    <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                    {!! Form::submit('Guardar',['class'=>'btn btn-success waves-effect']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <div class="widgets" id="widgets">
            <h4 class="column-title" style="padding: 10px;">Vista Previa</h4>
            @if($price != null)
            {!! Form::Price($price)!!}
            @endif
        </div>
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
    $(document).on('click', '.delete', function(event) {
        event.preventDefault();
        $(this).closest('tr').remove();
    });

    function addRow() {
        var html = "<tr>";
        html = html + "<td><input type='text' class='form-control' name='icono[]' required /></td><td><input type='text' class='form-control' name='item[]' required /></td><td><a style='color: #fff;' class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
        html = html + "</tr>";
        $('#items tr:last').after(html);
    }

    $('.contenido').on('focus', function() {

        original_name = $(this).attr('name');

        $(this).attr('name', 'contenido');

        CKEDITOR.replace('contenido', {
            height: 200,
            // By default, some basic text styles buttons are removed in the Standard preset.
            // The code below resets the default config.removeButtons setting.
            removeButtons: ''
        });

    });

    $('.contenido').on('blur', function() {

        $(this).attr('name', original_name);

    });
</script>
@endsection