<div class="container-fluid">
    <div class="row" style="font-size: 15px;">
            <div class="col-md-6" style="border: solid 1px black; padding-top: -20px;">
                <h4 align="center">Listado de Existencias <br>
                    <small> Bodega {{ $bodega }} </small></h4>
            </div>
            <div class="col-md-6" style="border: solid 1px black; padding-top: -20px;">
                <div style="vertical-align: center;">
                    {{ Form::label('fecha_corte', 'Fecha corte:') }} {{ $fecha_corte }}
                </div>
                Cantidad de registros: {{ $cantidad_registros }}
            </div>
    </div>
</div>
    