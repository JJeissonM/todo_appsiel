<div class="row" style="font-size: 14px; line-height: 1.5;">
    
    @include('contratos_transporte.contratos.logos_encabezado_print')
    
    @include('contratos_transporte.contratos.encabezado_titulo_y_numero_contrato', ['nro' => $p->nro] )

    <br>
    <table style="border: 1px solid; width: 100%; border-collapse: collapse;">
        <tbody>
            <tr>
                <th style="border: 1px solid; padding-left: 5px; font-weight: bold; text-align:center;" colspan="3">ANEXO DE PASAJEROS DE LOS SERVICIOS CONTRATADOS</th>
            </tr>
            <tr>
                <td style="border: 1px solid; padding-left: 5px; font-weight: bold; text-align:center; width:45px;">Nro.</td>
                <td style="border: 1px solid; padding-left: 5px; font-weight: bold; text-align:center;">IDENTIFICACIÓN</td>
                <td style="border: 1px solid; padding-left: 5px; font-weight: bold; text-align:center;">NOMBRE COMPLETO</td>
            </tr>
            <?php  
                $i = 1;
            ?>
            @foreach($c->contratogrupous as $p)
                <tr>
                    <td style="border: 1px solid; padding-left: 5px; text-align:center;">{{$i}}</td>
                    <td style="border: 1px solid; padding-left: 5px;">{{$p->identificacion}}</td>
                    <td style="border: 1px solid; padding-left: 5px;">{{$p->persona}}</td>
                </tr>
                <?php  
                    $i++;
                ?>
            @endforeach
        </tbody>
    </table>
</div>

