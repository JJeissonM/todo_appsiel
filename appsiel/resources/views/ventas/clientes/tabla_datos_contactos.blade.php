<div class="table-responsive">
	<h5 style="width: 100%; text-align: center;">Datos de Contacto(s)</h5>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Nombre</th>
				<th>Teléfono</th>
				<th>Email</th>
			</tr>
		</thead>
		<?php 
			$contactos = $registro->contactos;
		?>
		@foreach( $contactos AS $contacto )
			<tr>
				<td>
					{{ $contacto->tercero->descripcion }}
				</td>
				<td>
					{{ $contacto->tercero->telefono1 }}
				</td>
				<td>
					{{ $contacto->tercero->email }}
				</td>
			</tr>
		@endforeach
	</table>
</div>