<h4 align="center">Reporte de fallas</h4>
<h5 align="center">Curso {{$curso->descripcion}} </h5>
<div class="row">
        <div class="col-sm-4">
            <b>Fecha inicial: </b> {{ $fecha_inicial }}
        </div>
        <div class="col-sm-4">
           <b> Fecha final: </b> {{$fecha_final }}
        </div>
        <div class="col-sm-4">
            &nbsp;
        </div>
</div>
<div class="table-responsive">
<table class="table table-bordered table-striped">
	<thead>
	    <tr>
	        <th>Estudiante</th>
	        <th>Cant. fallas</th>
	        <th>Detalles</th>
	    </tr>
	</thead>
	<tbody>
	    <?php $i=0; ?>
	    @foreach ($matriculas as $matricula)
			<?php 
				$fallas = $registros->where('id_estudiante',$matricula->estudiante->id)->where('asistio','No');
			?>
	    	<tr>
	            <td> {{ $matricula->estudiante->tercero->descripcion }} </td>
	            <td align="center"> {{ $fallas->count() }} </td>
	            <td>
					<ul>
						@foreach ($fallas as $falla)
							<li> 
								Fecha: {{ $falla->fecha }} <br>
								Asignatura: {{ $falla->asignatura->descripcion }} <br>
								Anotación: {{ $falla->anotacion }} <br>
							</li>
						@endforeach
					</ul> 	
				</td>
	        </tr>
	        <?php
				$i += $registros->where('id_estudiante',$matricula->estudiante->id)->where('asistio','No')->count();
			?>
	    @endforeach
	    	<tr>
	            <td> Total fallas</td>
	            <td align="center"> {{ $i }} </td>
	            <td> &nbsp;</td>
	        </tr>
	</tbody>
</table>