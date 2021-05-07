<div class="row">
	<div class="col-md-12">
		<div class="container-fluid" id="div_control_cheques" style="display: none; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">
			<h5>Ingreso datos de cheque</h5>
			<hr>
			<div class="row">
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsFecha( 'fecha_emision', date('Y-m-d'), 'Fecha emisión', [], []) }}
					</div>
				</div>
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsFecha( 'fecha_cobro', date('Y-m-d'), 'Fecha cobro', [], []) }}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsText( 'numero_cheque', null, 'Número de cheque', []) }}
					</div>
				</div>
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsText( 'referencia_cheque', null, 'Referencia	', []) }}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsSelect('entidad_financiera_id', null, 'Entidad financiera', $entidades_financieras, []) }}
					</div>
				</div>
				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						{{ Form::bsTextArea( 'detalle', null, 'Detalle', []) }}
					</div>
				</div>
			</div>
        </div>
	</div>
</div>

			
	            			