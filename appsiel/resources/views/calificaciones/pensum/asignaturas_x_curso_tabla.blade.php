<h3>Lista de asignaturas del curso <small>({{ count($registros_asignados->toArray()) }} registros. )</small></h3>
<hr>

&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-primary" id="btn_actualizar_lista" > <i class="fa fa-refresh"></i> Actualizar lista</button>
<br><br>

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="lista_asignaciones">
    	{{ Form::bsTableHeader(['Orden boletín','Área','Descripción','Intensidad horaria','Maneja calificación','Acción']) }}
        <tbody>
	    	<?php 
	        	$ih_total = 0;
	        ?>
	        @foreach($registros_asignados as $fila)
	            <?php 
	                if ( $fila->maneja_calificacion ) 
	                {
	                    $maneja_calificacion = 'Si';
	                }else{
	                    $maneja_calificacion = 'No';
	                }

					$user = App\AcademicoDocente\AsignacionProfesor::get_user_segun_curso_asignatura( $fila->curso_id, $fila->asignatura_id, $fila->periodo_lectivo_id );

					if ( !is_null( $user ) )
		            {
		                $profesor = $user->name;
		            }else{
		                $profesor = 'No';
		            }

	            ?>

	            @include( 'calificaciones.pensum.asignaturas_x_curso_tabla_fila', [  
	            												'orden_boletin' => $fila->orden_boletin,
	            												'area_descripcion' => $fila->area,
	            												'asignatura_descripcion' => $fila->descripcion,
	            												'intensidad_horaria' => $fila->intensidad_horaria, 
	            												'maneja_calificacion' => $maneja_calificacion,
	            												'periodo_lectivo_id' => $fila->periodo_lectivo_id,
	            												'curso_id' => $fila->curso_id,
	            												'asignatura_id' => $fila->id,
	            												'profesor' => $profesor
	            											] )
	            											
	            <?php 
	            	$ih_total += $fila->intensidad_horaria;
	            ?>
	        @endforeach

        </tbody>
        
        <tfoot>
        	<tr>
        		<td colspan="3"></td>
            	<td> 
            		<div id="ih_total"> {{ $ih_total }} </div> 
            	</td>
            	<td colspan="2"> </td>
            </tr>
        </tfoot>

    </table>
</div>