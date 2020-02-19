<h3 style="width: 100%; text-align: center;"> Resumen de consultas </h3>
<hr>

<table class="table table-bordered">
    <tr>
        <td> <b> Total consultas: </b> </td> <td colspan="2"> {{ $total_consultas }}</td>
    </tr>
    <tr>
        <td rowspan="2"> <b> Pacientes atendidos: </b> </td> <td> <b>Nuevos:</b> </td> <td> {{ $total_pacientes_nuevos }}</td>
    </tr>
    <tr>
        <td> <b> Antiguos: </b> </td>  </td> <td> {{ $total_pacientes_antiguos }}</td>
    </tr>
</table>

<table id="myTable" class="table table-striped tabla_registros" style="margin-top: -4px;">
    <thead>
        <tr>
            <th> Fecha </th>
            <th> No. Consulta </th>
            <th> Paciente (H.C.) </th>
            <th> Tipo Consulta </th>
            <th class="columna_oculta" style="display: none;"> Acción </th>
        </tr>
    </thead>
    <tbody>
        <?php
        	foreach ($consultas as $consulta)
        	{ 
        ?>        
        	<tr>
                <td> {{ $consulta->fecha }} </td>
                <td> {{ $consulta->id }} </td>
                <td> {{ $consulta->paciente_nombre_completo }} ({{ $consulta->codigo_historia_clinica}}) </td>
                <td> {{ $consulta->tipo }} </td>
                <td class="columna_oculta" style="display: none;"> {{ Form::bsBtnVer( 'consultorio_medico/pacientes/'.$consulta->paciente_id.'?id=18&id_modelo=95' ) }} </td>
            </tr>
        <?php
    	   } // END FOREACH
    	?>
    </tbody>
</table>