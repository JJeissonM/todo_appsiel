<div class="row" style="font-size: 15px; border: 1px solid; border-collapse: collapse;">
        <div class="col-md-6" style="border: solid 1px black; padding-top: -20px;">
            <?php
                if ( $bodega == 'TODAS' ) {
                    $nom_bodega = "TODAS";
                    $bodega_id = 0;
                }else{
                    $nom_bodega = $bodega->descripcion;
                    $bodega_id = $bodega->id;
                }
            ?>
            <h2 align="center">Existencia bodega {{ $nom_bodega }}</h2>
        </div>
        <div class="col-md-6" style="border: solid 1px black; padding-top: -20px;">
            <div style="vertical-align: center;">
                <br/>
                <input type="hidden" name="bodega_id" value="{{ $bodega_id }}" id="bodega_id">
                {{ Form::label('fecha_corte', 'Fecha corte:') }} {{ $fecha_corte }}
            </div>
        </div>
</div>