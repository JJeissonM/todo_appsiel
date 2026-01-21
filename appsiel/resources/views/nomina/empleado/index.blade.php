@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-12">
			<div class="marco_formulario">
				<h3>Mi hoja de vida</h3>
				@if ( is_null($contrato) )
					<div class="alert alert-warning">
						<i class="fa fa-exclamation-triangle"></i> No hay un contrato asociado a su usuario. Contacte con el área de Talento Humano para obtener acceso.
					</div>
				@else
					<div class="row">
						<div class="col-md-6">
							<h4>Datos personales</h4>
							<table class="table table-condensed">
								<tr>
									<th>Identificación</th>
									<td>{{ $contrato->tercero->numero_identificacion }}</td>
								</tr>
								<tr>
									<th>Nombre completo</th>
									<td>{{ $contrato->tercero->descripcion }}</td>
								</tr>
								<tr>
									<th>Dirección</th>
									<td>{{ $contrato->tercero->direccion1 }}</td>
								</tr>
								<tr>
									<th>Teléfono</th>
									<td>{{ $contrato->tercero->telefono1 }}</td>
								</tr>
								<tr>
									<th>Email</th>
									<td>{{ $contrato->tercero->email }}</td>
								</tr>
							</table>
						</div>
						<div class="col-md-6">
							<h4>Datos del contrato</h4>
							<table class="table table-condensed">
								<tr>
									<th>Cargo</th>
									<td>{{ optional($contrato->cargo)->descripcion ?? '-' }}</td>
								</tr>
								<tr>
									<th>Grupo</th>
									<td>{{ optional($contrato->grupo_empleado)->descripcion ?? '-' }}</td>
								</tr>
								<tr>
									<th>Salario</th>
									<td>{{ Form::TextoMoneda( $contrato->sueldo ) }}</td>
								</tr>
								<tr>
									<th>Fecha de ingreso</th>
									<td>{{ $contrato->fecha_ingreso }}</td>
								</tr>
								<tr>
									<th>Contrato hasta</th>
									<td>{{ $contrato->contrato_hasta ?: 'Indefinido' }}</td>
								</tr>
								<tr>
									<th>Estado</th>
									<td>{{ $contrato->estado }}</td>
								</tr>
								<tr>
									<th>EPS</th>
									<td>{{ optional($contrato->entidad_salud)->descripcion ?? '-' }}</td>
								</tr>
								<tr>
									<th>AFP</th>
									<td>{{ optional($contrato->entidad_pension)->descripcion ?? '-' }}</td>
								</tr>
								<tr>
									<th>ARL</th>
									<td>{{ optional($contrato->entidad_arl)->descripcion ?? '-' }}</td>
								</tr>
								<tr>
									<th>Fondo cesantías</th>
									<td>{{ optional($contrato->entidad_cesantias)->descripcion ?? '-' }}</td>
								</tr>
								<tr>
									<th>Caja compensación</th>
									<td>{{ optional($contrato->entidad_caja_compensacion)->descripcion ?? '-' }}</td>
								</tr>
							</table>
						</div>
					</div>
				@endif
			</div>
		</div>
	</div>

	<br>

	<div class="row">
		<div class="col-md-12">
			<div class="marco_formulario">
				<h3>Desprendibles de pago</h3>
				@if ( is_null($contrato) )
					<p class="text-muted">Debe tener un contrato activo para poder consultar sus desprendibles.</p>
				@elseif ( $documentos->isEmpty() )
					<div class="alert alert-info">
						No se han generado desprendibles de pago para su contrato aún.
					</div>
				@else
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-condensed">
							<thead>
								<tr>
									<th>Fecha</th>
									<th>Documento</th>
									<th>Descripción</th>
									<th>Tipo</th>
									<th>Devengos</th>
									<th>Deducciones</th>
									<th>Neto</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($documentos as $documento)
									@php
										$neto = $documento->total_devengos - $documento->total_deducciones;
										$verUrl = url('nomina/empleado/desprendible/' . $documento->id);
										$pdfUrl = url('nomina_pdf_reporte_desprendibles_de_pago') . '?nom_doc_encabezado_id=' . $documento->id . '&core_tercero_id=' . $contrato->core_tercero_id;
									@endphp
									<tr>
										<td>{{ $documento->fecha }}</td>
										<td>{{ $documento->get_label_documento() }}</td>
										<td>{{ $documento->descripcion }}</td>
										<td>{{ $documento->tipo_liquidacion }}</td>
										<td>{{ Form::TextoMoneda($documento->total_devengos) }}</td>
										<td>{{ Form::TextoMoneda($documento->total_deducciones) }}</td>
										<td>{{ Form::TextoMoneda($neto) }}</td>
										<td>
											<a href="#" class="btn btn-primary btn-xs btn-ver-desprendible" data-url="{{ $verUrl }}">Ver</a>
											<a href="{{ $pdfUrl }}" target="_blank" class="btn btn-default btn-xs">PDF</a>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<div id="mensaje_desprendible"></div>
					<div id="vista_desprendible"></div>
				@endif
			</div>
		</div>
	</div>

@endsection

@section('scripts')
	<script type="text/javascript">
		$(function () {
			$('.btn-ver-desprendible').on('click', function (event) {
				event.preventDefault();
				var contenedor = $('#vista_desprendible');
				var mensaje = $('#mensaje_desprendible');
				var url = $(this).data('url');

				mensaje.html('');
				contenedor.html('<div class="alert alert-info"><i class="fa fa-spinner fa-pulse"></i> Cargando desprendible ...</div>');

				$.get(url, function (response) {
					contenedor.html(response.html);
					$('html, body').animate({
						scrollTop: contenedor.offset().top - 80
					}, 450);
				}).fail(function () {
					contenedor.html('');
					mensaje.html('<div class="alert alert-danger">No se pudo cargar el desprendible seleccionado. Intente nuevamente.</div>');
				});
			});
		});
	</script>
@endsection
