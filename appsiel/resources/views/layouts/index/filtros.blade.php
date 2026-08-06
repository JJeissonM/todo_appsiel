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
				<form method="get" action="{{ route('web.index') }}">
					<input type="hidden" name="id" value="{{ $id_app }}">
					<input type="hidden" name="id_modelo" value="{{ $id_modelo }}">
					<input type="hidden" name="nro_registros" value="{{ $nro_registros }}">
					<input type="hidden" name="search" value="{{ $search }}">

					<div class="row">
						@foreach($filtros_avanzados as $nombre => $filtro)
							<div class="col-md-3 col-sm-6 form-group">
								<label for="{{ $nombre }}">{{ $filtro['label'] }}</label>
								@if($filtro['type'] === 'combobox')
									<select name="{{ $nombre }}" id="{{ $nombre }}" class="combobox">
										@foreach($filtro['options'] as $valor => $etiqueta)
											<option value="{{ $valor }}" {{ (string) Input::get($nombre) === (string) $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
										@endforeach
									</select>
								@else
									<input type="date" name="{{ $nombre }}" id="{{ $nombre }}" value="{{ Input::get($nombre) }}" class="form-control">
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
@endif
