@if(!empty($filtros_avanzados))
	<?php
		$filtros_index_activos = false;
		foreach (array_keys($filtros_avanzados) as $nombre_filtro) {
			if (trim((string) Input::get($nombre_filtro, '')) !== '') {
				$filtros_index_activos = true;
				break;
			}
		}
	?>
	<div class="panel panel-default" style="margin: 15px 0;">
		<div class="panel-heading" style="padding: 10px 15px;">
			<a role="button" data-toggle="collapse" href="#filtros-avanzados-index" aria-expanded="{{ $filtros_index_activos ? 'true' : 'false' }}">
				<i class="fa fa-filter"></i> Filtros avanzados
			</a>
		</div>
		<div id="filtros-avanzados-index" class="panel-collapse collapse {{ $filtros_index_activos ? 'in' : '' }}">
			<div class="panel-body">
				<form method="get" action="{{ route('web.index') }}" id="form-filtros-avanzados-index">
					<input type="hidden" name="id" value="{{ $id_app }}">
					<input type="hidden" name="id_modelo" value="{{ $id_modelo }}">
					<input type="hidden" name="nro_registros" value="{{ $nro_registros }}">
					<input type="hidden" name="search" value="{{ $search }}">

					<div class="row">
						@foreach($filtros_avanzados as $nombre => $filtro)
							<div class="col-md-3 col-sm-6 form-group">
								<label for="{{ $nombre }}">{{ $filtro['label'] }}</label>
								@if($filtro['type'] === 'combobox')
									{{ Form::select($nombre, $filtro['options'], Input::get($nombre), ['id' => $nombre, 'class' => 'combobox filtro-avanzado-index']) }}
								@else
									<input type="date" name="{{ $nombre }}" id="{{ $nombre }}" value="{{ Input::get($nombre) }}" class="form-control filtro-avanzado-index">
								@endif
							</div>
						@endforeach
					</div>

					<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filtrar</button>
					<a href="{{ route('web.index', ['id' => $id_app, 'id_modelo' => $id_modelo, 'nro_registros' => $nro_registros]) }}" class="btn btn-default"><i class="fa fa-eraser"></i> Limpiar</a>
				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function () {
			function sincronizarComboboxFiltro($select, $input) {
				if ($input.length === 0) {
					return;
				}

				var textoActual = $.trim($input.val());
				var textoSeleccionado = $.trim($select.find('option:selected').text());

				if (textoActual === textoSeleccionado) {
					return;
				}

				// Al borrar o modificar el texto se descarta inmediatamente
				// la selección anterior del elemento <select> oculto.
				$select.val('');
				if (textoActual === '') {
					return;
				}

				$select.find('option').each(function () {
					if ($.trim($(this).text()).toLowerCase() === textoActual.toLowerCase()) {
						$select.val($(this).val());
						return false;
					}
				});
			}

			function sincronizarComboboxesFiltros() {
				$('#form-filtros-avanzados-index').find('select.combobox').each(function () {
					var $select = $(this);
					var $input = $select.next('.custom-combobox').find('.custom-combobox-input');
					sincronizarComboboxFiltro($select, $input);
				});
			}

			$(document).on('input', '#form-filtros-avanzados-index .custom-combobox-input', function () {
				var $input = $(this);
				var $select = $input.closest('.custom-combobox').prev('select.combobox');
				sincronizarComboboxFiltro($select, $input);
			});

			$('#form-filtros-avanzados-index').on('submit', function () {
				sincronizarComboboxesFiltros();
				$(this).find('input[name="search"]').val(
					$('#form-busqueda-index').find('input[name="search"]').val()
				);
			});

			$('#form-busqueda-index').on('submit', function () {
				var $formBusqueda = $(this);
				sincronizarComboboxesFiltros();

				$('#form-filtros-avanzados-index').find('.filtro-avanzado-index').each(function () {
					var campo = this;
					$formBusqueda.find('input[type="hidden"]').filter(function () {
						return this.name === campo.name;
					}).val($(campo).val());
				});
			});
		});
	</script>
@endif
