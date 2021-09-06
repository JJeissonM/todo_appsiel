<div class="container-fluid">
    <div class="row" style="font-size: 15px;">
            <div class="col-md-6" style="border: solid 1px black; padding-top: -20px;">
                <h2 align="center">Existencia bodega {{ $bodega }}</h2>
            </div>
            <div class="col-md-6" style="border: solid 1px black; padding-top: -20px;">
                <div style="vertical-align: center;">
                    {{ Form::label('fecha_corte', 'Fecha corte:') }} {{ $fecha_corte }}
                </div>
            </div>
    </div>
</div>
    