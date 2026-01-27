@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-sm-8">
							<h3 style="margin:0;">Todos los cuestionarios creados</h3>
							<p class="text-muted" style="margin:0;">Aquí puede revisar cualquier cuestionario y duplicarlo bajo su usuario.</p>
						</div>
						<div class="col-sm-4">
							<div class="form-group" style="margin-bottom:0;">
								<input id="cuestionarioSearch" type="search" class="form-control input-sm" placeholder="Buscar cuestionario..." />
							</div>
						</div>
					</div>
				</div>
				<div class="panel-body">
					@if(session('flash_message'))
						<div class="alert alert-success">
							{{ session('flash_message') }}
						</div>
					@endif
					<div class="table-responsive">
						<table class="table table-bordered table-striped" id="cuestionariosTable">
							<thead>
								<tr>
									<th>Nombre</th>
									<th>Tipo ICFES</th>
									<th>Preguntas</th>
									<th>Creado por</th>
									<th>Estado</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
								@forelse($cuestionarios as $cuestionario)
									<tr>
										<td>{{ $cuestionario->descripcion }}</td>
										<td>{{ $cuestionario->tipo_icfes_label ?: 'General' }}</td>
										<td>{{ $cuestionario->preguntas->count() }}</td>
										<td>{{ $cuestionario->created_by_user->name }}</td>
										<td>{{ $cuestionario->estado }}</td>
										<td>
											<button type="button" class="btn btn-info btn-xs btn-preview" data-cuestionario="{{ $cuestionario->id }}"><i class="fa fa-eye"></i> Previsualizar</button>
											<form method="POST" action="{{ route('cuestionarios.duplicar', ['cuestionario_id' => $cuestionario->id]) }}?id={{ Input::get('id') }}&id_modelo={{ Input::get('id_modelo') }}" style="display:inline;">
												{{csrf_field()}}
												<input type="hidden" name="id" value="{{ Input::get('id') }}">
												<button type="submit" class="btn btn-success btn-xs"><i class="fa fa-copy"></i> Duplicar</button>
											</form>
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="6">No hay cuestionarios disponibles.</td>
									</tr>
								@endforelse
								<tr class="no-results" style="display:none;">
									<td colspan="6">No hay cuestionarios que coincidan con la búsqueda.</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="cuestionarioPreviewModal" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Previsualizar cuestionario</h4>
				</div>
				<div class="modal-body" id="contenidoPreview">
					<div class="text-center text-muted">Cargando...</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('scripts')
	<script>
		$(function(){
			$('.btn-preview').on('click', function(){
				const cuestionarioId = $(this).data('cuestionario');
				$('#contenidoPreview').html('<div class="text-center text-muted">Cargando...</div>');
				$('#cuestionarioPreviewModal').modal('show');
				$.get("{{ route('cuestionarios.preview', ['cuestionario_id' => 0]) }}".replace('/0', '/' + cuestionarioId), function(html){
					$('#contenidoPreview').html(html);
				});
			});
			const $rows = $('#cuestionariosTable tbody tr').not('.no-results');
			$('#cuestionarioSearch').on('input', function(){
				const term = $(this).val().toLowerCase();
				let visible = 0;
				$rows.each(function(){
					const text = $(this).text().toLowerCase();
					const match = text.indexOf(term) >= 0;
					$(this).toggle(match);
					if (match) visible++;
				});
				$('.no-results').toggle(visible === 0);
			});
		});
	</script>
@endsection
