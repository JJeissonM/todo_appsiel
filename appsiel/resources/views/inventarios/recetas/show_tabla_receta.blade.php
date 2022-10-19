<div class="row">
    <div class="col-md-4" style="padding:5px;"> 
        <b>Receta: </b> {{ $platillo->item_platillo->descripcion }}
    </div>
    <div class="col-md-4" style="padding:5px;"> 
        <b>Unid. Medida: </b> {{ $platillo->item_platillo->unidad_medida1 }}
    </div>
    <div class="col-md-4" style="padding:5px;"> 
        <b>Categoría: </b> {{ $platillo->item_platillo->grupo_inventario->descripcion }}
    </div>	    	
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="myTable">
            <thead>
                <tr>
                    <th>ID item ingred.</th>
                    <th>Ingrediente (U.M.)</th>
                    <th>Cant. x una(1) porción</th>
                    <th>Costo unit.</th>
                    <th>Costo total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $sum_costo_total = 0;
                ?>
                @foreach( $ingredientes as $linea)
                    <?php
                        $sum_costo_total += $linea['cantidad_porcion'] * $linea['ingrediente']->get_costo_promedio(0);
                    ?>
                    <tr>
                        <td>{{ $linea['ingrediente']->id }}</td>
                        <td>{{ $linea['ingrediente']->descripcion }} ({{ $linea['ingrediente']->unidad_medida1 }})</td>
                        <td align="center">{{ number_format( $linea['cantidad_porcion'], 2, ',', '.') }}</td>
                        <td align="right">${{ number_format( $linea['ingrediente']->get_costo_promedio(0), 2, ',', '.') }}</td>
                        <td align="right">${{ number_format( $linea['cantidad_porcion'] * $linea['ingrediente']->get_costo_promedio(0), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td> &nbsp; </td>
                    <td> &nbsp; </td>
                    <td> &nbsp; </td>
                    <td> &nbsp; </td>
                    <td align="right"> ${{ number_format( $sum_costo_total, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<hr>