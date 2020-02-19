<div class="table-responsive">
	
	<h3 align="center">
		Intensidad Horaria por Asignatura. 
		<br> 
		Año lectivo: {{ Form::select('periodo_lectivo_id', $periodos_lectivos, $periodo_lectivo->id, ['id' => 'periodo_lectivo_id'] ) }}

		<a href="{{ url( 'calificaciones/revisar_asignaciones?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&periodo_lectivo_id='.$periodo_lectivo->id ) }}" class="btn btn-info btn-bg" id="btn_actualizar">Actualizar</a>

	</h3>

    <table class="table table-bordered table-striped" id="lista_asignaciones">
    	
    	<thead>
        	<tr>
        		<th> Asignatura </th>
        		@foreach ($todos_los_cursos as $un_curso) 
			       <th> {{ $un_curso->codigo }} </th>
			    @endforeach
			    <th>Tot. x Asig.</th>
			</tr>
		</thead>

    	<tbody>
	    	<?php 
	        	$total_ih_curso = [];
		        $total_asig_curso = [];

		        if ( !is_null($todos_los_cursos) )
		        {
		            $total_ih_curso = array_fill(0, count( $todos_los_cursos->toArray() ), 0);
		            $total_asig_curso = array_fill(0, count( $todos_los_cursos->toArray() ), 0);
		        }

		        $i=0;
	        ?>
	        <tr>
	        @foreach($todas_las_asignaturas as $una_asignatura)
	        	<tr>
	        		<td> {{ $una_asignatura->descripcion }} </td>

		        	<?php 
		        		$j=0;
	        			$total_ih_asignatura = 0;
	        		?>
		        	@foreach ($todos_los_cursos as $un_curso)
		        		<?php 
		        			$asignacion = $todas_las_asignaciones->where( 'periodo_lectivo_id', $periodo_lectivo->id )
							                                ->where( 'curso_id', $un_curso->id )
							                                ->where( 'id', $una_asignatura->id )
							                                ->first();
	                    ?>

	                    <?php
	                    	$ih = '';
			                if ( !is_null($asignacion) ) 
			                {
			                	$ih = $asignacion->intensidad_horaria;
								$total_ih_asignatura += $ih;
					        	$total_ih_curso[$j] += $ih;
			                
			                    $total_asig_curso[$j]++;
			                }
				        	$j++;
	                    ?>
	                    <td align="center"> {{ $ih }} </td>
				        	
				    @endforeach

			        <td align="center"> {{ $total_ih_asignatura }} </td>
	        	</tr>
	        @endforeach

        </tbody>

        <tfoot>
			<tr>
				<td>
					Total x Curso
				</td>
				<?php
					$total_ih = 0;
				?>
		        	@for ($i=0; $i < count($total_ih_curso) ; $i++)
		        		<td align="center"> 
		        			{{ $total_ih_curso[$i] }}h 
		        			<hr> {{ $total_asig_curso[$i] }} asig.
		        		</td>

		        		<?php 
		        			$total_ih += $total_ih_curso[$i];
		        		?>
		        	@endfor        	
        		<td align="center"> {{ $total_ih }}h </td>
        	</tr>
        </tfoot>
    </table>
</div>