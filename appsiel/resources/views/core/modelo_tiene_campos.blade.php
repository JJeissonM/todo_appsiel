@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">	<?php
				echo "<h4>Moldelos que tienen asignado el campo ".$campos->descripcion."</h4>";
				echo '<table class="table table-striped">
						<tr style="font-weight: bold;">
							<td>ID</td>
							<td>Modelo</td>
							<td>Ubicación</td>
							<td>Modelo relacionado</td>
							<td>Home miga de pan</td>
							<td>Acción</td>
						</tr>';
				foreach ($modelos as $fila) {
					echo '<tr>
							<td>'.$fila->id.'</td>
							<td>'.$fila->descripcion.'</td>
							<td>'.$fila->name_space.'</td>
							<td>'.$fila->modelo_relacionado.'</td>
							<td>'.$fila->home_miga_pan.'</td>
							<td> <a class="btn btn-primary btn-xs btn-detail" href="'.url( 'web/'.$fila->id.'?id=7&id_modelo=3').'" title="Ver" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a> </td>
						</tr>';
				}

				echo '</table>';
			?>
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

		});
	</script>
@endsection