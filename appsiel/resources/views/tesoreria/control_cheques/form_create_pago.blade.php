<div class="container-fluid" id="div_control_cheques" style="display: none; border: 1px solid #ddd; border-radius: 4px; background-color: #e1faff;">
	<div class="row">
		<div class="col-md-4">
				<h5>Cheques activos</h5>
				<hr>
				<div class="row">
					<div class="col-md-12">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('cheque_id', null, 'Pagar con cheque', $cheques_activos, []) }}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div id="valor_cheque_seleccionado" style="display: none;"></div>
					</div>
				</div>
	    </div>
		<div class="col-md-8">
			<div id="div_ingreso_cheques">
				@include('tesoreria.control_cheques.form_create')
			</div>				
        </div>
	</div>
</div>
<br>            			