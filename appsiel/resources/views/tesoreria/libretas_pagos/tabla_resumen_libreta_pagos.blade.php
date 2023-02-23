<h3>Libreta de pagos</h3>
<div class="row">
	<div class="col-md-12 botones-gmail">
		{{ Form::bsBtnPrint( url('tesoreria/imprimir_libreta/'.$libreta->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))  ) }}

		<?php 
			$se_puede_editar_libreta = true;
			foreach($plan_pagos as $fila)
			{
				if( !empty( $fila->facturas_estudiantes->toArray() ) )
				{
					$se_puede_editar_libreta = false;
				}
			}
		?>

		@if($se_puede_editar_libreta)
			{{ Form::bsBtnEdit( url('tesoreria/editar_libreta/'.$libreta->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))  ) }}
		@endif
		
	</div>
</div>
<div class="table-responsive">
	<table class="table table-bordered table-striped">
		{{ Form::bsTableHeader(['Vlr. matrícula','Fecha inicio','Vlr. pensión anual','Núm. periodos','Vlr. pensión mensual','Estado']) }}
		<tbody>
				<tr class="info">
					<td class="text-right"><?php echo number_format($libreta->valor_matricula, 0, ',', '.')?></td>
					<td>{{$libreta->fecha_inicio}}</td>
					<td class="text-right"><?php echo number_format($libreta->valor_pension_anual, 0, ',', '.')?></td>
					<td>{{$libreta->numero_periodos}}</td>
					<td class="text-right"><?php echo number_format($libreta->valor_pension_mensual, 0, ',', '.')?></td>
					<td>{{$libreta->estado}}</td>
				</tr>
		</tbody>

	</table>
</div>