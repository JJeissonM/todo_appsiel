<div class="table-responsive">
	<h5 style="width: 100%; text-align: center;">Datos Auditoría</h5>
	<table class="table table-bordered">
		<tr>
			<td>
				<b>Fecha creación:</b> {{ $registro->created_at }}
			</td>
			<td>
				<b>Fecha modificación:</b> {{ $registro->updated_at }}
			</td>
		</tr>
		<!-- <tr>
			<td>
				<b>Creado por:</b> { { explode('@',$registro->creado_por)[0] }}
			</td>
			<td>
				<b>Modificado por:</b> { { explode('@',$registro->modificado_por)[0] }}
			</td>
		</tr>
    -->
	</table>
</div>
	