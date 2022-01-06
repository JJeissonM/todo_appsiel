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

			@if($anamnesis!='')
				<br>
				<h3 style="margin-bottom: 1px;">Anamnesis</h3>
				{!! $anamnesis !!}
			@endif


			@if($examenes!='')
				<br>
				<h3 style="margin-bottom: -25px;">Exámenes</h3>
				{!! $examenes !!}
			@endif			
			
			<?php
				$examenes2 = App\Salud\ExamenMedico::examenes_del_paciente2( $consulta->paciente_id, $consulta->id );
			?>
			@if(!empty($examenes2->toArray()))
				<br>
				<h3 style="margin-bottom: 1px;">Fórmula</h3>
				@foreach($examenes2 as $examen)
					<?php
						//$formula = App\Salud\FormulaOptica::where('paciente_id', $consulta->paciente_id)->where('consulta_id', $consulta->id)->first();
						$formula = App\Salud\FormulaOptica::leftJoin('salud_formula_tiene_examenes','salud_formula_tiene_examenes.formula_id','=','salud_formulas_opticas.id')
													->where('salud_formulas_opticas.paciente_id', $consulta->paciente_id)
													->where('salud_formulas_opticas.consulta_id', $consulta->id)
													->where('salud_formula_tiene_examenes.examen_id', $examen->id)
													->get()
													->first();
					?>
					@include('consultorio_medico.formula_optica_show_tabla', [ 'formula' => $formula ] )
				@endforeach
			@endif
			
			@if(!empty($diagnosticos->toArray()))
				<br>
				<h3 style="margin-bottom: 1px;">Diagnóstico(s)</h3>
				@include('consultorio_medico.diagnostico_cie.show_tabla', [ 'diagnosticos' => $diagnosticos ] )
			@endif

			@if($resultados!='')
				<br>
				<h3 style="margin-bottom: -25px;">Resultados</h3>
				{!! $resultados !!}
			@endif

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