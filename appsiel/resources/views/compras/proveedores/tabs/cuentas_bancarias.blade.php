<?php
	$url_parametros = 'id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion');
?>

<br>

<div class="panel panel-default">
	<div class="panel-heading"><strong>Agregar cuenta bancaria</strong></div>
	<div class="panel-body">
		<form method="POST" action="{{ url('compras_proveedores/' . $registro->id . '/cuentas_bancarias?' . $url_parametros) }}">
			{{ csrf_field() }}
			<div class="row">
				<div class="col-md-3">
					<label>Entidad financiera</label>
					<select name="entidad_financiera_id" class="form-control" required>
						<option value="">Seleccionar...</option>
						@foreach($entidades_financieras as $entidad)
							<option value="{{ $entidad->id }}">{{ $entidad->descripcion }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2">
					<label>Tipo cuenta</label>
					<select name="tipo_cuenta" class="form-control" required>
						<option value="">Seleccionar...</option>
						<option value="Ahorros">Ahorros</option>
						<option value="Corriente">Corriente</option>
					</select>
				</div>
				<div class="col-md-2">
					<label>Número cuenta</label>
					<input type="text" name="numero_cuenta" class="form-control" maxlength="80" required>
				</div>
				<div class="col-md-3">
					<label>Ciudad</label>
					<select name="codigo_ciudad" class="form-control" required>
						<option value="">Seleccionar...</option>
						@foreach($ciudades as $ciudad)
							<option value="{{ $ciudad->id }}">{{ $ciudad->ciudad }}{{ is_null($ciudad->departamento) ? '' : ', ' . $ciudad->departamento }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-1">
					<label>Estado</label>
					<select name="estado" class="form-control" required>
						<option value="Activo">Activo</option>
						<option value="Inactivo">Inactivo</option>
					</select>
				</div>
				<div class="col-md-1 text-right">
					<label>&nbsp;</label>
					<button type="submit" class="btn btn-primary btn-block">
						<i class="fa fa-save"></i>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="table-responsive">
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Entidad financiera</th>
				<th>Tipo cuenta</th>
				<th>Número cuenta</th>
				<th>Ciudad</th>
				<th>Estado</th>
				<th style="width: 220px;">Acciones</th>
			</tr>
		</thead>
		<tbody>
			@forelse($cuentas_bancarias as $cuenta)
				<tr class="js-fila-cuenta" data-cuenta-id="{{ $cuenta->id }}">
					<td>{{ $cuenta->id }}</td>
					<td>{{ $cuenta->entidad_financiera ? $cuenta->entidad_financiera->descripcion : '' }}</td>
					<td>{{ $cuenta->tipo_cuenta }}</td>
					<td>{{ $cuenta->numero_cuenta }}</td>
					<td>{{ $cuenta->ciudad ? $cuenta->ciudad->descripcion : $cuenta->codigo_ciudad }}</td>
					<td>{{ $cuenta->estado }}</td>
					<td>
						<a class="btn btn-xs btn-info js-btn-editar-cuenta" href="#editar-cuenta-{{ $cuenta->id }}" data-cuenta-id="{{ $cuenta->id }}">
							<i class="fa fa-pencil"></i> Editar
						</a>
						<form method="POST" action="{{ url('compras_proveedores/' . $registro->id . '/cuentas_bancarias/' . $cuenta->id . '?' . $url_parametros) }}" style="display:inline;">
							{{ csrf_field() }}
							{{ method_field('DELETE') }}
							<button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('¿Desea eliminar esta cuenta bancaria?');">
								<i class="fa fa-trash"></i> Eliminar
							</button>
						</form>
					</td>
				</tr>
				<tr class="collapse" id="editar-cuenta-{{ $cuenta->id }}">
					<td colspan="7">
						<form method="POST" action="{{ url('compras_proveedores/' . $registro->id . '/cuentas_bancarias/' . $cuenta->id . '?' . $url_parametros) }}">
							{{ csrf_field() }}
							{{ method_field('PUT') }}
							<div class="row">
								<div class="col-md-3">
									<label>Entidad financiera</label>
									<select name="entidad_financiera_id" class="form-control" required>
										<option value="">Seleccionar...</option>
										@foreach($entidades_financieras as $entidad)
											<option value="{{ $entidad->id }}" {{ (int)$cuenta->entidad_financiera_id === (int)$entidad->id ? 'selected="selected"' : '' }}>
												{{ $entidad->descripcion }}
											</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-2">
									<label>Tipo cuenta</label>
									<select name="tipo_cuenta" class="form-control" required>
										<option value="Ahorros" {{ $cuenta->tipo_cuenta === 'Ahorros' ? 'selected="selected"' : '' }}>Ahorros</option>
										<option value="Corriente" {{ $cuenta->tipo_cuenta === 'Corriente' ? 'selected="selected"' : '' }}>Corriente</option>
									</select>
								</div>
								<div class="col-md-2">
									<label>Número cuenta</label>
									<input type="text" name="numero_cuenta" class="form-control" maxlength="80" value="{{ $cuenta->numero_cuenta }}" required>
								</div>
								<div class="col-md-3">
									<label>Ciudad</label>
									<select name="codigo_ciudad" class="form-control" required>
										<option value="">Seleccionar...</option>
										@foreach($ciudades as $ciudad)
											<option value="{{ $ciudad->id }}" {{ (string)$cuenta->codigo_ciudad === (string)$ciudad->id ? 'selected="selected"' : '' }}>
												{{ $ciudad->ciudad }}{{ is_null($ciudad->departamento) ? '' : ', ' . $ciudad->departamento }}
											</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-1">
									<label>Estado</label>
									<select name="estado" class="form-control" required>
										<option value="Activo" {{ $cuenta->estado === 'Activo' ? 'selected="selected"' : '' }}>Activo</option>
										<option value="Inactivo" {{ $cuenta->estado === 'Inactivo' ? 'selected="selected"' : '' }}>Inactivo</option>
									</select>
								</div>
								<div class="col-md-1 text-right">
									<label>&nbsp;</label>
									<button type="submit" class="btn btn-success btn-block">
										<i class="fa fa-save"></i>
									</button>
								</div>
							</div>
						</form>
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="7" class="text-center">No hay cuentas bancarias registradas.</td>
				</tr>
			@endforelse
		</tbody>
	</table>
</div>

<script type="text/javascript">
	(function () {
		function setFilaEdicion( filaEdicion, abrir ) {
			if (!filaEdicion) {
				return;
			}

			var cuentaId = filaEdicion.id.replace('editar-cuenta-', '');
			var filaResumen = document.querySelector('.js-fila-cuenta[data-cuenta-id="' + cuentaId + '"]');

			if (abrir) {
				filaEdicion.classList.add('in');
				filaEdicion.style.display = 'table-row';
				if (filaResumen) {
					filaResumen.style.display = 'none';
				}
				return;
			}

			filaEdicion.classList.remove('in');
			filaEdicion.style.display = 'none';
			if (filaResumen) {
				filaResumen.style.display = '';
			}
		}

		document.addEventListener('click', function (e) {
			var boton = e.target.closest('.js-btn-editar-cuenta');
			if (!boton) {
				return;
			}

			e.preventDefault();

			var cuentaId = boton.getAttribute('data-cuenta-id');
			var filaActual = document.getElementById('editar-cuenta-' + cuentaId);
			var estaAbierta = filaActual && filaActual.classList.contains('in');

			var todas = document.querySelectorAll('tr[id^="editar-cuenta-"]');
			for (var i = 0; i < todas.length; i++) {
				if (todas[i] !== filaActual) {
					setFilaEdicion(todas[i], false);
				}
			}

			setFilaEdicion(filaActual, !estaAbierta);
		});
	})();
</script>
