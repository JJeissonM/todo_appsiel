<div class="table-responsive">
    <code>Algunos productos arrojan saldos negativos. Por favor verifique las existencias de inventarios.</code>
    <p> <b>Bodega:</b> {{ $bodega->descripcion }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Fecha:</b> {{ $fecha_corte }} </p>
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader( $lbl_encabezados ) }}
        <tbody>
            @foreach( $items AS $item )
	            <tr>
                    @if(config('inventarios.codigo_principal_manejo_productos') != 'referencia')
                        <td class="text-center">{{ $item->item_id }}</td>
                    @endif
                    
                    @if(config('inventarios.codigo_principal_manejo_productos') == 'referencia')
                        <td class="text-center">{{ $item->referencia }}</td>
                    @endif
	                
	                <td>{{ $item->descripcion }}</td>
	                <td class="text-center">{{ number_format($item->existencia, 2, ',', '.') }}</td> 
	                <td class="text-right"> {{ number_format($item->cantidad_facturada, 2, ',', '.') }} </td>
	                <td class="text-right"> {{ number_format($item->nuevo_saldo, 2, ',', '.') }} </td>
	            </tr>
            @endforeach
        </tbody>
    </table>
</div>