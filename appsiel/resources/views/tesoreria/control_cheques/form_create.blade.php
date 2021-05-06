<h5>Ingreso datos de cheque</h5>
<hr>
<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsFecha( 'fecha_cobro', date('Y-m-d'), 'Fecha de cobro', [], []) }}
		</div>
	</div>
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText( 'numero_cheque', null, 'Número de cheque', ['required'=>'required']) }}
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsText( 'referencia_cheque', null, 'Referencia	', []) }}
		</div>
	</div>
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsTextArea( 'detalle', null, 'Detalle', []) }}
		</div>
	</div>
</div>
	            			