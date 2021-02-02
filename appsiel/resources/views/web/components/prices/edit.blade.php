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

@section('content')<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="text-align: center; font-weight: bold; padding: 15px;">
            <h4>.:: En ésta Sección: Price (Planes de Precios) ::.</h4>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body d-flex justify-content-between flex-wrap">
        <div id="wrapper">
            <h4 class="column-title" style="padding: 10px;">Editar Plan</h4>
            <div class="col-md-12">
                {!! Form::model($item,['route'=>['prices.editar',$item],'method'=>'PUT','class'=>'form-horizontal','files'=>'true'])!!}
                <input type="hidden" name="widget_id" value="{{$widget}}">
                <input type="hidden" name="variables_url" value="{{$variables_url}}">
                <input type="hidden" name="price_id" value="{{$price->id}}">
                <div class="form-group">
                    <label>URL Para el Botón</label>
                    <span data-toggle="tooltip" title="Establece el enlace para mostrar información detallada del plan."> <i class="fa fa-question-circle"></i></span>
                    <input name="url" value="{{$item->url}}" type="text" placeholder="Url que abrirá el botón" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Precio</label>
                    <span data-toggle="tooltip" title="Establece el precio para el plan."> <i class="fa fa-question-circle"></i></span>
                    <input name="precio" value="{{$item->precio}}" type="text" placeholder="Precio del plan, ejemplo: $50,000/mes" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Color del Texto (Ítems del plan)</label>
                    <span data-toggle="tooltip" title="Establece el color para los items o caracteristicas del plan."> <i class="fa fa-question-circle"></i></span>
                    <input type='color' value="{{$item->text_color}}" class='form-control' name='text_color' required>
                </div>
                <div class="form-group">
                    <label>Imagen de la Cabecera(400x200 px)</label>
                    <label>Actual (<a target="_blank" href="{{asset($item->imagen_cabecera)}}">Ver Imágen</a>)</label>
                    <input name="imagen_cabecera" type="file" placeholder="Archivo de Imagen" class="form-control">
                </div>
                <div class="form-group">
                    <label>Color del Botón 1</label>
                    <span data-toggle="tooltip" title="Establece un color para los iconos de los items del plan."> <i class="fa fa-question-circle"></i></span>
                    <input type='color' value="{{$item->button_color}}" class='form-control' name='button_color' required>
                </div>
                <div class="form-group">
                    <label>Color del Botón 2</label>
                    <span data-toggle="tooltip" title="Establece un color para el boton del plan."> <i class="fa fa-question-circle"></i></span>
                    <input type='color' class='form-control' value="{{$item->button2_color}}" name='button2_color' required>
                </div>
                <div class="form-group">
                    <label>Color del Fondo del Plan</label>
                    <span data-toggle="tooltip" title="Establece un color para el fondo del plan."> <i class="fa fa-question-circle"></i></span>
                    <input type='color' class='form-control' value="{{$item->background_color}}" name='background_color' required>
                </div>
                <div class="table-responsive col-md-12" id="table_content">
                    <h4>Ítems del Plan <span data-toggle="tooltip" title="Establece los items o caracteristicas del plan."> <i class="fa fa-question-circle"></i></span></h4>                    
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
                            @if($lista!=null)
                            @foreach($lista as $l)
                            <tr>
                                <td><input type='text' class='form-control' name='icono[]' value="{{$l->icono}}" required /></td>
                                <td><input type='text' class='form-control' name='item[]' value="{{$l->item}}" required /></td>
                                <td><a style='color: #fff;' class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>
                            </tr>
                            @endforeach
                            @endif
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

    $('[data-toggle="tooltip"]').tooltip({
        animated: 'fade',
        placement: 'right',
        html: true
    });
</script>

@endsection