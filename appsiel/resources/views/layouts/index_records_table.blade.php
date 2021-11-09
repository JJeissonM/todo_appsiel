<table class="table table-bordered table-striped table-hover" id="tbDatos">
	{{ Form::bsTableHeader($encabezado_tabla) }}
	<tbody>

		@foreach ($registros as $fila)
		<?php
			$totalElementos = count($fila->toArray());
		?>
		<tr>
			<td>
				<input type="checkbox" value="{{$fila['campo'.$totalElementos]}}" class="btn-gmail-check">
			</td>
			<?php for ($i = 1; $i < $totalElementos; $i++) { ?>
				<td class="table-text">
					@if($url_ver != '' )
						<a href="{{url('').'/'.str_replace("id_fila", $fila['campo'.$totalElementos], $url_ver)}}" style="display: block; text-decoration: none;color: inherit;" title="Consultar">
							<div style="width: 100%;height: 100%;">
								{{ $fila['campo'.$i] }}
							</div>
						</a>
					@else
						<div style="width: 100%;height: 100%;">
							{{ $fila['campo'.$i] }}
						</div>
					@endif
				</td>
			<?php } ?>
		</tr>
		@endforeach
	</tbody>
</table>