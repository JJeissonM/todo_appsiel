<h3 style="width:100%;text-align:center;">Balance de comprobación</h3>
<p style="text-align:center;">Desde {{ $fecha_desde }} hasta {{ $fecha_hasta }}</p>
<hr>
<div class="table-responsive">
    <table id="myTable" data-excel-format="xlsx" data-excel-filename="balance_de_comprobacion" class="table table-striped" style="margin-top:-4px;width:100%;">
        <colgroup>
            <col style="width:{{ $detallar_terceros ? 24 : 40 }}%;">
            @if($detallar_terceros)<col style="width:24%;">@endif
            @for($columna = 0; $columna < 4; $columna++)<col style="width:{{ $detallar_terceros ? 13 : 15 }}%;">@endfor
        </colgroup>
        <thead>
            <tr>
                <th>Cuenta</th>
                @if($detallar_terceros)<th>NIT / Tercero</th>@endif
                <th>Saldo inicial</th><th>Mov. Débito</th><th>Mov. Crédito</th><th>Saldo Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $fila)
                <?php
                    $fondo = $fila->tipo === 'clase' ? '#90a4ae' : ($fila->tipo === 'grupo' ? ($fila->nivel === 1 ? '#b0bec5' : '#cfd8dc') : '#ffffff');
                    $negrita = in_array($fila->tipo, ['clase', 'grupo', 'cuenta']) ? 'bold' : 'normal';
                ?>
                <tr class="fila-{{ $fila->tipo }}" data-nivel="{{ $fila->nivel }}" style="background-color:{{ $fondo }};font-weight:{{ $negrita }};">
                    <td style="padding-left:{{ $fila->nivel * 2 }}rem;">{{ $fila->tipo !== 'tercero' ? $fila->descripcion : '' }}</td>
                    @if($detallar_terceros)<td>{{ $fila->tipo === 'tercero' ? $fila->descripcion : '' }}</td>@endif
                    @foreach($fila->valores as $valor)
                        <td align="right" class="text-right" data-excel-value="{{ $valor }}">{{ number_format($valor, 0, ',', '.') }}</td>
                    @endforeach
                </tr>
            @endforeach
            <tr class="total-general" style="background-color:#90a4ae;font-weight:bold;">
                <td colspan="{{ $detallar_terceros ? 2 : 1 }}">TOTAL GENERAL</td>
                @foreach($totales as $valor)
                    <td align="right" class="text-right" data-excel-value="{{ $valor }}">{{ number_format($valor, 0, ',', '.') }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
</div>
