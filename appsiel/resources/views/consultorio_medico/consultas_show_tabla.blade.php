<table class="table table-bordered">
	<tr>
		<td>
			<b>Fecha:</b> {{ $consulta->fecha }}
		</td>
		<td>
			<b>Tipo Consulta:</b> {{ $consulta->tipo_consulta }}
		</td>
		<td>
			<b>Acompañado por:</b> {{ $consulta->nombre_acompañante }} ({{ $consulta->parentezco_acompañante }}) ({{ $consulta->documento_identidad_acompañante }})
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<b>Diagnóstico:</b> {{ $consulta->diagnostico }}
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<b>Indicaciones:</b> {{ $consulta->indicaciones }}
		</td>
	</tr>
</table>

<h3>Exámenes</h3>
<?php
	$examenes = App\Salud\ExamenMedico::examenes_del_paciente( $consulta->paciente_id, $consulta->id );

	$cantidad = 0;
	
	if ( !is_null($examenes) ) {
		$cantidad = count( $examenes->toArray() );
	}
?>

<!-- Este for MUESTRA un botón en cada iteración -->
@for($i = 0; $i < $cantidad; $i++ )
	{!! $examenes[$i] !!}
@endfor


<h3>Fórmula</h3>
<?php
	$formulas = App\Salud\FormulaOptica::where('paciente_id', $consulta->paciente_id)->where('consulta_id', $consulta->id)->get();
?>

@if( is_null($formulas) )
	@can('salud_consultas_create')
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'consultorio_medico/formulas_opticas/create?id='.Input::get('id').'&id_modelo='.$modelo_formulas_opticas->id.'&paciente_id='.$registro->id.'&consulta_id='.$consulta->id ) }}
	@endcan
@else

	<?php 
		$formula = $formulas[0]; 
		$examenes = DB::table('salud_formula_tiene_examenes')->leftJoin('salud_examenes','salud_examenes.id','=','salud_formula_tiene_examenes.examen_id')->where('formula_id', $formula->id)->select('salud_examenes.descripcion')->get();
	?>
	
	@can('salud_consultas_edit')
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'consultorio_medico/formulas_opticas/'.$formula->id.'/edit?id='.Input::get('id').'&id_modelo='.$modelo_formulas_opticas->id.'&paciente_id='.$registro->id.'&consulta_id='.$consulta->id ) }}
	@endcan
	
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'consultorio_medico/formulas_opticas/'.$formula->id.'/edit?id='.Input::get('id').'&id_modelo='.$modelo_formulas_opticas->id.'&paciente_id='.$registro->id.'&consulta_id='.$consulta->id ) }}

	<br><br>
	
	@include('consultorio_medico.formula_optica_show_tabla' )
@endif