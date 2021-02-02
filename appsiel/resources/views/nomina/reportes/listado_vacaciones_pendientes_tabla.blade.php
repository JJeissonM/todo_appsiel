<div class="table-responsive">
    <table id="myTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th colspan="11"> 
                	<h3 style="width: 100%; text-align: center;">Listado de vacaciones pendientes</h3>
                	<br>
                	<span style="width: 100%; text-align: center;"> Fecha de corte:  {{ $fecha_corte }}</span>
                	<hr>
                </th>
            </tr>
            <tr>
                <th> &nbsp; </th>
                <th> Empleado (CC) </th>
                <th width="150"> Grupo Empleados / <br> Cargo </th>
                <th> Fecha ingreso </th>
                <th> Sueldo <br> actual </th>
                <th> Fecha final <br> último periodo <br> pagado </th>
                <th> Fecha <br> corte </th>
                <th> No. periodos <b> pendientes </th>
                <th> Días pendientes </th>
                <th> Vlr. pendiente <br> por pagar </th>
                <th> Vlr. un periodo <br> (15 días) </th>
            </tr>
        </thead>
        <tbody>
	            <?php
	            	$total_dias_pendientes = 0;
	            	$total_valor_pendiente_por_pagar = 0;
	            	$total_valor_un_periodo_vacacion = 0;
	            ?>
        	@foreach( $vacaciones_pendientes as $fila )
        		<?php 
        			//dd($fila);
        		?>
	            <tr>
	                <td> {{ $fila['numero_fila'] }} </td>
	                <td> {{ $fila['datos']->empleado->tercero->descripcion }} ({{ $fila['datos']->empleado->tercero->numero_identificacion }}) </td>
	                <td> {{ $fila['datos']->empleado->grupo_empleado->descripcion }} / {{ $fila['datos']->empleado->cargo->descripcion }} </td>
	                <td> {{ $fila['datos']->empleado->fecha_ingreso }} </td>
	                <td> {{ number_format( $fila['datos']->empleado->sueldo, 0,',','.' ) }} </td>
	                <td> {{ $fila['datos']->fecha_final_ultimo_periodo_pagado }} </td>
	                <td> {{ $fila['fecha_corte'] }} </td>
                    <td> {{ number_format( $fila['datos']->dias_pendientes /15, 2,',','.' ) }} </td>
                    <td> {{ number_format( $fila['datos']->dias_pendientes, 2,',','.' ) }} </td>
	                <td> {{ number_format( $fila['datos']->valor_pendiente_por_pagar, 0,',','.' ) }} </td>
	                <td> {{ number_format( $fila['datos']->valor_un_periodo_vacacion, 0,',','.' ) }} </td>
	            </tr>
	            <?php
	            	$total_dias_pendientes += $fila['datos']->dias_pendientes;
	            	$total_valor_pendiente_por_pagar += $fila['datos']->valor_pendiente_por_pagar;
	            	$total_valor_un_periodo_vacacion += $fila['datos']->valor_un_periodo_vacacion;
	            ?>
            @endforeach
        </tbody>
        <tfoot>
			<tr style="background: #4a4a4a; color: white;">
				<td colspan="7">
					&nbsp;
				</td> 
                <td> {{ number_format( $total_dias_pendientes / 15, 2,',','.' ) }} </td>
                <td> {{ number_format( $total_dias_pendientes, 2,',','.' ) }} </td>
				<td> {{ number_format( $total_valor_pendiente_por_pagar, 0,',','.' ) }} </td>
				<td> {{ number_format( $total_valor_un_periodo_vacacion, 0,',','.' ) }} </td>
        	</tr>        	
        </tfoot>
    </table>
</div>
