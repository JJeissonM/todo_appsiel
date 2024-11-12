<h3 style="width: 100%; text-align: center;">
    Documentos de Facturación 
    <div style="font-size: 15px; text-align: center;">
        <b>{{ $empresa->descripcion }}</b><br/>
        <b>{{ config("configuracion.tipo_identificador") }}:
            @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
        {{ $empresa->direccion1 }}, {{ $empresa->ciudad->descripcion }} <br/>
        Teléfono(s): {{ $empresa->telefono1 }}<br/>
        <b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
    </div>
</h3>
<span style="color: rgb(58, 58, 58);">{!! $mensaje !!}</span> 
<hr>

<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th> Transacción </th>
                <th> Fecha </th>
                <th> Documento </th>
                <th> CC/NIT </th>
                <th> Cliente </th>
                @if($detalla_productos)
                    <th> Producto </th>
                    <th> Cantidad </th>
                @endif
                <th> Base IVA </th>
                <th> Vlr. IVA </th>
                <th> Total </th>
            </tr>
        </thead>
        <tbody>
            
            <?php 
                $gran_base_impuesto_total = 0;
                $gran_precio_total = 0;
                $gran_valor_iva = 0;
            ?>

@foreach($documentos_ventas as $documento)
    
    @if($detalla_productos)
        
        <?php 
            $lineas_registros = $documento->lineas_registros;
        ?>

        @foreach($lineas_registros as $linea)

            <?php 
                $signo = 1;
                if ( $linea->precio_total < 0 ) {
                    $signo = -1;
                }
                $gran_base_impuesto_total += abs($linea->base_impuesto_total) * $signo;
                $gran_precio_total += $linea->precio_total;

                $valor_iva = (abs($linea->base_impuesto_total) * $signo) * $linea->tasa_impuesto / 100;
                $gran_valor_iva += $valor_iva;
            ?>

            <tr>
                <td> {{ $documento->tipo_transaccion->descripcion }} </td>
                <td> {{ $documento->fecha }} </td>
                <td> {!! $documento->get_label_documento() !!} </td>
                <td> {{ $documento->cliente->tercero->numero_identificacion }} </td>
                <td> {{ $documento->cliente->tercero->descripcion }} </td>
                <td> {{ $linea->item->get_value_to_show() }} </td>
                <td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                <td> ${{ number_format( abs($linea->base_impuesto_total) * $signo, 0, ',', '.') }} </td>
                <td> ${{ number_format( $valor_iva, 0, ',', '.') }} </td>
                <td> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
            </tr>
        @endforeach
    @else
        <?php 
            $lineas_registros = $documento->lineas_registros;
            $base_impuesto_total = 0;
            $precio_total = 0;
            $valor_iva = 0;
            foreach($lineas_registros as $linea)
            {
                if ( $linea->id!=55 && $linea->id!=56) {
                    //dd($linea);
                }
                
                $signo = 1;
                if ( $linea->precio_total < 0 ) {
                    $signo = -1;
                }

                $base_impuesto_total += abs($linea->base_impuesto_total) * $signo;
                $precio_total += $linea->precio_total;

                $valor_iva += (abs($linea->base_impuesto_total) * $signo) * $linea->tasa_impuesto / 100;

                $gran_base_impuesto_total += abs($linea->base_impuesto_total) * $signo;
                $gran_precio_total += $linea->precio_total;
                $gran_valor_iva += $valor_iva;
            }
        ?>
            <tr>
                <td> {{ $documento->tipo_transaccion->descripcion }} </td>
                <td> {{ $documento->fecha }} </td>
                <td> {!! $documento->get_label_documento() !!} </td>
                <td> {{ $documento->cliente->tercero->numero_identificacion }} </td>
                <td> {{ $documento->cliente->tercero->descripcion }} </td>
                <td> ${{ number_format( $base_impuesto_total, 0, ',', '.') }} </td>
                <td> ${{ number_format( $valor_iva, 0, ',', '.') }} </td>
                <td> ${{ number_format( $precio_total, 0, ',', '.') }} </td>
            </tr>
    @endif                
@endforeach

@foreach($documentos_ventas_pos as $documento)
    
    @if($detalla_productos)
        
        <?php 
            $lineas_registros = $documento->lineas_registros;
        ?>

        @foreach($lineas_registros as $linea)

            <?php
                $signo = 1;
                if ( $linea->precio_total < 0 ) {
                    $signo = -1;
                }
                $gran_base_impuesto_total += abs($linea->base_impuesto_total) * $signo;
                $gran_precio_total += $linea->precio_total;
                
                $valor_iva = (abs($linea->base_impuesto_total) * $signo) * $linea->tasa_impuesto / 100;
                $gran_valor_iva += $valor_iva;
            ?>

            <tr>
                <td> {{ $documento->tipo_transaccion->descripcion }} </td>
                <td> {{ $documento->fecha }} </td>
                <td> {!! $documento->get_label_documento() !!} </td>
                <td> {{ $documento->cliente->tercero->numero_identificacion }} </td>
                <td> {{ $documento->cliente->tercero->descripcion }} </td>
                <td> {{ $linea->producto->get_value_to_show() }} </td>
                <td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                <td> ${{ number_format( abs($linea->base_impuesto_total) * $signo, 0, ',', '.') }} </td>
                <td> ${{ number_format( $valor_iva, 0, ',', '.') }} </td>
                <td> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
            </tr>
        @endforeach
    @else
        <?php 
            $lineas_registros = $documento->lineas_registros;
            $base_impuesto_total = 0;
            $precio_total = 0;
            $valor_iva = 0;
            foreach($lineas_registros as $linea)
            {
                $signo = 1;
                if ( $linea->precio_total < 0 ) {
                    $signo = -1;
                }
                $base_impuesto_total += abs($linea->base_impuesto_total) * $signo;
                $precio_total += $linea->precio_total;

                $valor_iva += (abs($linea->base_impuesto_total) * $signo) * $linea->tasa_impuesto / 100;

                $gran_base_impuesto_total += abs($linea->base_impuesto_total) * $signo;
                $gran_precio_total += $linea->precio_total;
                $gran_valor_iva += $valor_iva;
            }
        ?>
            <tr>
                <td> {{ $documento->tipo_transaccion->descripcion }} </td>
                <td> {{ $documento->fecha }} </td>
                <td> {!! $documento->get_label_documento() !!} </td>
                <td> {{ $documento->cliente->tercero->numero_identificacion }} </td>
                <td> {{ $documento->cliente->tercero->descripcion }} </td>
                <td> ${{ number_format( $base_impuesto_total, 0, ',', '.') }} </td>
                <td> ${{ number_format( $valor_iva, 0, ',', '.') }} </td>
                <td> ${{ number_format( $precio_total, 0, ',', '.') }} </td>
            </tr>
    @endif                
@endforeach


        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"></td>
                @if($detalla_productos)
                    <td colspan="2"></td>
                @endif
                <td> ${{ number_format( $gran_base_impuesto_total, 0, ',', '.') }} </td>
                <td> ${{ number_format( $gran_valor_iva, 0, ',', '.') }} </td>
                <td> ${{ number_format( $gran_precio_total, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>