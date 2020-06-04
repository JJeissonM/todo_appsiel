@extends('layouts.principal')

@section('webstyle')
<style>
	.page {
		padding: 50px;
		-webkit-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		-moz-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		font-size: 16px;
	}

	.border {
		border: 1px solid;
		padding: 5px;
	}
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		&nbsp;
		<div class="row" style="padding: 20px;">
			<div class="col-md-12">
				<h3>Planillas Generadas al Contrato</h3>
				<div class="table-responsive col-md-12" id="table_content">
					<table class="table table-bordered table-striped" id="myTable">
						<thead>
							<tr>
								<th>Nro.</th>
								<th>Objeto</th>
								<th>Fecha Celebrado</th>
								<th>Origen - Destino</th>
								<th>Vigencia</th>
								<th>Contratante</th>
								<th>Vehículo</th>
								<th>Contrato Como</th>
								<th>Planillas FUEC</th>
							</tr>
						</thead>
						<tbody>
							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		//$('.select2').select2();
	});
</script>
@endsection