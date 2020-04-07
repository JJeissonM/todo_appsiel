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
				<div class="panel panel-primary">
					<div class="panel-heading">Crear Contrato</div>
					<div class="panel-body">
						<div class="col-md-12" style="padding: 50px;">
							<div class="col-md-12 page">
								<table style="width: 100%;">
									<tbody>
										<tr>
											<td class="border" style="width: 40%;"><img style="width: 100%;" src="{{ asset('img/logos/transporcol_back.jpg') }}"></td>
											<td class="border" style="width: 20%; text-align: center;"><img style="width: 70%;" src="{{ asset('img/logos/super_transporte.png') }}"></td>
											<td class="border" style="width: 40%;"><img style="max-height: 150px;" src="{{ asset('img/logos/transporcol_rigth.jpg') }}"></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script>
	$(document).on('click', '.delete', function(event) {
		event.preventDefault();
		$(this).closest('tr').remove();
	});

	function addRow(tabla) {
		var html = "<tr>";
		if (tabla == 'obs') {
			html = html + "<td><input type='date' class='form-control' name='obs_fecha_suceso[]' /></td><td><input type='text' class='form-control' name='obs_observacion[]' required /></td><td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
		} else {
			html = html + "<td><input type='date' class='form-control' name='reporte_fecha_suceso[]' /></td><td><input type='text' class='form-control' name='reporte_reporte[]' required /></td><td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
		}
		html = html + "</tr>";
		$('#' + tabla + ' tr:last').after(html);
	}
</script>
@endsection