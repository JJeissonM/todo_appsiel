<h3 style="width: 100%; text-align: center;"> Listado de citas de control vencidas </h3>
<hr>

<table class="table table-bordered">
    <tr>
        <td> <b> Total citas vencidas: </b> </td> 
        <td> {{ $consultas['total_citas_vencidas'] }}</td>
        <td> <b> Fecha listado: </b> </td> 
        <td> {{ date('Y-m-d') }}</td>
    </tr>
    <tr>
        <td> <b> Total citas próximas a vencer: </b> </td> 
        <td> {{ $consultas['total_citas_proximas_a_vencer'] }}</td>
        <td> &nbsp; </td> 
        <td> &nbsp; </td>
    </tr>
</table>

<table id="myTable" class="table table-striped">
    <thead>
        <tr>
            <th> Fecha última consulta </th>
            <th> No. Consulta </th>
            <th> Paciente (H.C.) </th>
            <th> Próximo control </th>
            <th> Fecha vencimiento </th>
            <th> Estado </th>
            <th> Días </th>
            <th class="columna_oculta" style="display: none;"> Acción </th>
        </tr>
    </thead>
    <tbody>
        <?php
            unset($consultas['total_citas_vencidas']);
            unset($consultas['total_citas_proximas_a_vencer']);
        	foreach ($consultas as $consulta)
        	{ 
                if ( $consulta['fecha_control'] > date('Y-m-d') ) {
                    $class = 'warning';
                    $estado = 'Próxima a vencer';
                }else{
                    $class = 'danger';
                    $estado = 'Vencida';
                }
        ?>        
        	<tr class="{{ $class }}">
                <td> {{ $consulta['fecha'] }} </td>
                <td> {{ $consulta['id'] }} </td>
                <td> {{ $consulta['paciente_nombre_completo'] }} ({{ $consulta['codigo_historia_clinica']}}) </td>
                <td> {{ $consulta['proximo_control'] }} </td>
                <td> {{ $consulta['fecha_control'] }} </td>
                <td> {{ $estado }} </td>
                <td> {{ $consulta['dias'] }} </td>
                <td class="columna_oculta" style="display: none;"> {{ Form::bsBtnVer( 'consultorio_medico/pacientes/'.$consulta['paciente_id'].'?id=18&id_modelo=95' ) }} </td>
            </tr>
        <?php
    	   } // END FOREACH
    	?>
    </tbody>
</table>