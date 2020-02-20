@extends('layouts.principal')

@section('content')
	

	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-lg-10 col-lg-offset-1">
			<div align="center">
				<a class="btn btn-primary btn-sm" href="{{ url('/academico_docente/listar_estudiantes/curso_id/'.$curso->id.'/id_asignatura/'.$asignatura->id) }}"><i class="fa fa-btn fa-print"></i> Imprimir planilla</a>
			</div>

			<br/>

			<h4> <b> Curso     :</b> {{ $curso->descripcion }} </h4> 
			<h4> <b> Asignatura:</b> {{ $asignatura->descripcion }} </h4> 

			<br/>

			<div class="table-responsive">
				<table class="table table-bordered table-striped" id="myTable">
					<thead>
						<tr>
							<th>Nombre</th>
							<th>Documento</th>
							<th>Género</th>
							<th>Fecha nacimiento</th>
							<th>Dirección</th>
							<th>Barrio</th>
							<th>Teléfono</th>
							<th>OBSERVADOR</th>
						</tr>
					</thead>

					<tbody>
						@foreach ($estudiantes as $estudiante)
							<?php 
								$edad = calcular_edad($estudiante->fecha_nacimiento);
							?>
							<tr>
								<td width="300px"> {{ $estudiante->nombre_completo }} </td>
								<td>{{ $estudiante->tipo_y_numero_documento_identidad }}</td>
								<td>{{ $estudiante->genero }}</td>
								<td>{{ $estudiante->fecha_nacimiento }} <br/> ({{ $edad }}) </td>
								<td>{{ $estudiante->direccion1 }}</td>
								<td>{{ $estudiante->barrio }}</td>
								<td>{{ $estudiante->telefono1 }}</td>
								<td style="text-align: left;">

									<?php

										$url_1 = 'academico_docente/novedad_observador/show_observador/'.$estudiante->id_estudiante.'?id='.Input::get('id').'&curso_id='.$curso->id.'&asignatura_id='.$asignatura->id;

										$url_2 = 'academico_docente/valorar_aspectos_observador/'.$estudiante->id_estudiante.'?id='.Input::get('id').'&curso_id='.$curso->id.'&asignatura_id='.$asignatura->id;

										$url_3 = 'academico_docente/novedad_observador?estudiante_id='.$estudiante->id_estudiante.'&id='.Input::get('id').'&curso_id='.$curso->id.'&asignatura_id='.$asignatura->id.'&id_modelo=17';

										$url_4 = 'academico_docente/dofa_observador?estudiante_id='.$estudiante->id_estudiante.'&id='.Input::get('id').'&curso_id='.$curso->id.'&asignatura_id='.$asignatura->id.'&id_modelo=18';
									?>

									<?php
								  		$select_crear = '<div class="dropdown" style="display:inline-block;">
								            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-scale"></i> Gestionar
								            <span class="caret"></span></button>
								            <ul class="dropdown-menu">';

						                $select_crear.='<li><a href="'.url( $url_1 ).'"> Consultar</a></li>';
						            ?>

					            	@hasrole('Director de grupo') 
					            		<?php
					            			$select_crear.='<li><a href="'.url( $url_2 ).'"> Aspectos</a></li>';
										?>
									@endhasrole

						            <?php

						                $select_crear.='<li><a href="'.url( $url_3 ).'"> Novedades</a></li>';

						                $select_crear.='<li><a href="'.url( $url_4 ).'"> DOFA</a></li>';

								        $select_crear.='</ul>
								          </div>';

								          echo $select_crear;
								  	?>								

								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection

@section('scripts')

<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script>
	$(document).ready( function () {
		$('#myTable').DataTable();
		@yield('j_query')
	} );
</script>
@endsection

<?php
	function calcular_edad($fecha_nacimiento){
		$datetime1 = new DateTime($fecha_nacimiento);
		$datetime2 = new DateTime('now');
		$interval = $datetime1->diff($datetime2);
		$edad=$interval->format('%R%a');
		return floor($edad/365)." Años";
	}
?>