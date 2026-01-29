
<style>
	table th {
		padding: 15px;
		text-align: center;
		border-bottom: solid 2px;
		background-color: #E5E4E3;
	}

	table td {
		padding: 2px;
	}
	#tabla_lineas_registros_calificaciones{
		color: white;
	}

	#tabla_lineas_registros_calificaciones td{
		color: white;
		background-color: white;
	}

	#tabla_lineas_registros_calificaciones th{
		color: white;
		background-color: white;
		border: 0px solid;
	}
</style>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<h4 style="text-align: center;">
			Ingreso de {{ $titulo }}
			<br>
			Año lectivo: {{ $periodo_lectivo->descripcion }}
		</h4>
		<hr>		

		<div class="row">
			<div class="col-sm-12">
				<b>Año:</b><code>{{ $anio }}</code>
				<b>Periodo:</b> <code>{{ $periodo->descripcion }}</code>
				<b>Curso:</b><code>{{ $curso->descripcion }}</code>
				<b>Asignatura:</b><code>{{ $datos_asignatura->descripcion }}</code>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<h4><i class="fa fa-info-circle"> &nbsp; </i>Use las flechas de dirección y tabular para desplazarse: &nbsp;<i class="fa fa-arrow-down"></i>&nbsp;<i class="fa fa-arrow-up"></i>&nbsp;<b>TAB </b></h4>
			</div>
			</br></br>
		</div>

		<p style="color: gray; text-align: right;" id="mensaje_formulario">

			<spam id="mensaje_sin_guardar" style="background-color:#eaabab; display: none;">
				Sin guardar</spam>

			<spam id="mensaje_guardando" style="background-color:#a3e7fe; display: none;">
				Guardando...</spam>

			<spam id="mensaje_guardadas" style="background-color: #b1e6b2;">
				Calificaciones guardadas</spam>
		</p>

		<div class="row">
			<div class="col-sm-12">

				@yield('tabla')

			</div>
		</div>

		<div style="text-align: center; width: 100%;">
			<button class="btn btn-primary btn-xs" id="bs_boton_guardar" disabled="disabled"> <i class="fa fa-save"></i> Guardar</button>

			<span class="counter" style="color:#9c27b0"></span>

		</div>

		<div class="row">
			<div class="col-sm-12">
				{{ Form::open( [ 'url' => 'calificaciones/almacenar_calificacion', 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'formulario'] ) }}

					{{ Form::hidden('escala_min', $escala_min_max[0], ['id' =>'escala_min']) }}
					{{ Form::hidden('escala_max', $escala_min_max[1], ['id' =>'escala_max']) }}

					{{ Form::hidden('id_colegio', $id_colegio, ['id' =>'id_colegio']) }}
					{{ Form::hidden('creado_por', $creado_por, ['id' =>'creado_por']) }}
					{{ Form::hidden('modificado_por', $modificado_por, ['id' =>'modificado_por']) }}
					{{ Form::hidden('id_periodo', $periodo->id, ['id' =>'id_periodo']) }}
					{{ Form::hidden('curso_id', $curso->id, ['id' =>'curso_id']) }}
					{{ Form::hidden('anio', $anio, ['id' =>'anio']) }}
					{{ Form::hidden('id_asignatura', $datos_asignatura->id, ['id' =>'id_asignatura']) }}
					{{ Form::hidden('cantidad_estudiantes', $cantidad_estudiantes, ['id' =>'cantidad_estudiantes']) }}

{{ Form::hidden('id_app',Input::get('id')) }} 
{{ Form::hidden('return', $ruta ) }}

@include('calificaciones.partials.selector_popup')

{{ Form::bsHidden( 'hay_pesos', $hay_pesos ) }}

					{{ Form::bsHidden( 'lineas_registros_calificaciones', 0 ) }}
				{{Form::close()}}
				
				<div class="table-responsive">
					<table class="table" id="tabla_lineas_registros_calificaciones" style="color: white; border: 1px solid white;">
						<thead>
							<tr>
								<th>id_calificacion</th>
								<th>id_calificacion_aux</th>
								<th>codigo_matricula</th>
								<th>matricula_id</th>
								<th>fila_id</th>
								<th>id_estudiante</th>
								@for($c=1; $c < 16; $c++) 
									<th>C{{$c}}</th>
								@endfor
								<th>calificacion</th>
								<th>logros</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
</div>

@include('components.design.ventana_modal', [ 'titulo' => 'Ingreso/Actualización encabezados de calificaciones', 'texto_mensaje' => 'Registro actualizado correctamente.'])

<script src="{{ asset( 'assets/js/calificaciones/form_create_v2.js?aux=' . uniqid() )}}"></script>


<script src="{{ asset( 'assets/js/calificaciones/form_create_v2_encabezados.js?aux=' . uniqid() )}}"></script>
