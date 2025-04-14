
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

				@yield('tabla')

			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				{{ Form::open( [ 'url' => 'calificaciones/almacenar_calificacion', 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'formulario'] ) }}

					{{ Form::hidden('periodo_id', $periodo->id, ['id' =>'periodo_id']) }}
					{{ Form::hidden('curso_id', $curso->id, ['id' =>'curso_id']) }}
					{{ Form::hidden('asignatura_id', $datos_asignatura->id, ['id' =>'asignatura_id']) }}

					{{ Form::hidden('id_app',Input::get('id')) }} 
					{{ Form::hidden('return', $ruta ) }}

				{{Form::close()}}
			</div>
		</div>

	</div>
</div>

@include('components.design.ventana_modal', [ 'titulo' => 'Ingreso/Actualización encabezados de calificaciones', 'texto_mensaje' => 'Registro actualizado correctamente.'])

<script src="{{ asset( 'assets/js/calificaciones/desempenios/form_create_v2.js?aux=' . uniqid() )}}"></script>