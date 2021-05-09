<table class="table table-striped" id="myTable">
    {{ Form::bsTableHeader( ['Inmueble','Propietario','Saldo pend.'] ) }}
    <tbody>
        <?php


        $total_1 = 0;
        $total_2 = 0;
        $total_3 = 0;
        for($i=0;$i<count($movimiento_cxc);$i++)
        { 
            
            $propiedad = App\PropiedadHorizontal\Propiedad::where('id',$movimiento_cxc[$i]['codigo_referencia_tercero'])->where('core_empresa_id',Auth::user()->empresa_id)->get();

            if ( count($propiedad) > 0) {
                $propiedad = $propiedad[0];
            }else{
                $propiedad = (object)array('codigo' => 0);
            }

            ?>
            <tr>
                <td class="text-center"> {{ $propiedad->codigo }}</td>
                <td> {{ $movimiento_cxc[$i]['tercero'] }} </td>
                <td class="col_saldo_pendiente text-right" > {{ number_format($movimiento_cxc[$i]['saldo_pendiente'], 0, ',', '.') }} </td>
            </tr>
        <?php 
            $total_3+=$movimiento_cxc[$i]['saldo_pendiente'];
        } 
        ?>
        <tr>
            <td>  </td>
            <td>  </td>
            <td class="text-right"> {{ number_format($total_3, 0, ',', '.') }} </td>
        </tr>
    </tbody>
</table>