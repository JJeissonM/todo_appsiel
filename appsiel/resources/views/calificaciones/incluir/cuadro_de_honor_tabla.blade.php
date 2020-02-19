@foreach($salida as $key => $value)
    <p style="width: 100%; text-align: center; font-size: 20px; font-weight: bold;"> Cuadro de Honor {{ $agrupado}} {{ $key }} </p>
    <p style="width: 100%; text-align: center; font-size: 16px; font-weight: bold;">{{ $periodo }}</p>
    <hr>    

    <table id="myTable" class="table table-striped tabla_contenido" style="margin-top: -4px;">
        <thead>
            <tr>
                <th> Puesto </th>
                <th> Estudiante </th>
                <th> Curso </th>
                <th> Promedio </th>
            </tr>
        </thead>
        <tbody> 
            @php $i=0; @endphp
            @foreach($value as $estudiante)
                
                @if( $i == $cantidad_puestos )
                    @php break; @endphp
                @endif
            	
                @php
                    $imagen = ''; 
                    if ($mostrar_foto) 
                    {
                        $url = config('configuracion.url_instancia_cliente')."/storage/app/fotos_terceros/".$estudiante['imagen'];
                        $imagen = '<img src="'.$url.'" style="width: 85px; height: 110px; border-radius: 4px;"> <br>';
                    }
                @endphp


                <tr class="fila-{{$i}}" >
                    <td>
                       <p style="width: 100%; text-align: center; font-size: 18px;"> {{ $i+1 }}° </p>
                    </td>
                    <td>
                       <p style="width: 100%; text-align: center;"> {!! $imagen !!} {{ $estudiante['nombre_completo'] }} </p>
                    </td>
                    <td>
                       <p style="width: 100%; text-align: center;">{{ $estudiante['Curso'] }} </p>
                    </td>
                    <td>
                       <p style="width: 100%; text-align: center;">{{ number_format($estudiante['calificacion_prom'], 2, ',', '.') }} </p>
                    </td>
                </tr>
                @php $i++; @endphp
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

@endforeach