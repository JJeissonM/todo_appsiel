<div class="col-md-12">
    @include( 'contratos_transporte.contratos.logos_encabezado_print', compact('empresa') )
    
    @include('contratos_transporte.contratos.encabezado_titulo_y_numero_contrato', ['nro' => $p->nro])

    <table style="width: 100%; line-height: 0.9;">
        <tbody>
            <tr>
                <td class="border" style="width: 100%; padding: 10px; font-size: 10px">
                    <p style=" text-align: center; font-weight: bold; font-size: 16px;">{{$v->titulo_atras}}</p>
                    @if(count($v->plantillaarticulos)>0)
                        @foreach($v->plantillaarticulos as $a)
                            <p style="text-align: justify;"><b>{{$a->titulo}}</b> {{$a->texto}}</p>
                            @if(count($a->plantillaarticulonumerals)>0)
                                @foreach($a->plantillaarticulonumerals as $pan)
                                    <p style="text-align: justify;"><b>{{$pan->numeracion}}</b> {{$pan->texto}}</p>
                                    @if(count($pan->numeraltablas)>0)
                                        <?php $total = count($pan->numeraltablas);
                                            $mitad = 0;
                                            if ($total % 2 == 0) {
                                                $mitad = $total / 2;
                                            } else {
                                                $mitad = $total / 2;
                                                $mitad = $mitad + 0.5;
                                            }
                                        ?>
                                        <table style="width: 100%;">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <table style="width: 100%;">
                                                            <tbody>
                                                                <?php $i = 0; ?>
                                                                @foreach($pan->numeraltablas as $n)
                                                                    <?php $i = $i + 1; ?>
                                                                    @if($i<=$mitad) 
                                                                    <tr>
                                                                        <td class="border">{{$n->campo}}</td>
                                                                        <td class="border">{{$n->valor}}</td>
                                                                    </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td>
                                                        <table style="width: 100%;">
                                                            <tbody>
                                                                <?php $i = 0; ?>
                                                                @foreach($pan->numeraltablas as $n)
                                                                <?php $i = $i + 1; ?>
                                                                @if($i>$mitad)
                                                                <tr>
                                                                    <td class="border">{{$n->campo}}</td>
                                                                    <td class="border">{{$n->valor}}</td>
                                                                </tr>
                                                                @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <p>No hay artículos en la plantilla</p>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>