@extends('layouts.pdf')

@section('content')

	<div class="row lbl_historia_clinica">
			
			@include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', ['vista'=>'imprimir'] )

			<!-- Datos básicos del paciente -->
			@include('consultorio_medico.pacientes_datos_historia_clinica_show',['mostrar_avatar'=>false])

			<!-- Datos básicos del Consulta -->
			@include( 'consultorio_medico.consultas.datos_consulta' )

			@if( (int)config('consultorio_medico.mostrar_datos_laborales_paciente') )
	        	@include('consultorio_medico.pacientes.datos_laborales')
	        @endif

			<br>
			<h3>Anamnesis</h3>
			{!! $anamnesis !!}

			<br>
			<!-- Datos de Exámenes--> 
			<h3>Exámenes</h3>
			{!! $examenes !!}

			<br>
			<h3>Fórmula</h3>
			<?php
				$formula = App\Salud\FormulaOptica::where('paciente_id', $consulta->paciente_id)->where('consulta_id', $consulta->id)->first();
			?>

			@include('consultorio_medico.formula_optica_show_tabla' )

			<br>
			<h3>Resultados</h3>
			{!! $resultados !!}

			<br><br>

			<p class="pull-right">
				_________________________________ <br>
				{{ $profesional_salud->nombre_completo }} <br>
				{{ $profesional_salud->especialidad }} <br>
				{{ $profesional_salud->numero_carnet_licencia }}
			</p>
	</div>
	<br/>
	<footer>
		<hr>
		{{ $empresa->descripcion }}, Dirección: {{ $empresa->direccion1 }} Tel. {{ $empresa->telefono1 }}
	</footer>
@endsection