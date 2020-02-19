@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<?php
		use App\Http\Controllers\Sistema\VistaController;
	?>

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Modificando el registro</h4>
		    <hr>

		    @if( isset($url_action) )
		    	{{ Form::model($registro, ['url' => [$url_action], 'method' => 'PUT','files' => true]) }}
			@else
				{{ Form::model($registro, ['url' => ['web/'.$registro->id], 'method' => 'PUT','files' => true]) }}
			@endif

			
				
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }

				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}


			{{ Form::close() }}

			<br/><br/>

			<div class="row">
				<div class="col-md-8 col-md-offset-2">
					<h4>Lista de campos</h4>
					<hr>
					<table class="table table-striped" id="tabla_lista_campos">
						<thead>
							<tr>
								<th>Etiqueta</th>
								<th>Tipo</th>
								<th>Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($lista_campos as $fila): ?>
								<tr>
									<td>{{ $fila->etiqueta }}</td>
									<td>{{ $fila->tipo }}</td>
									<?php if ($fila->opciones = ""): ?>
										<td>{{ $fila->opciones }}</td>
									<?php else: ?>
										<td> <button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button> </td>
									<?php endif ?>
									
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>

					<div class="row">
						<div class="col-md-10 col-md-offset-1" style="vertical-align: center; border: 1px solid gray;">
							{{ Form::open(array('url'=>'web/guardar_asignacion')) }}
								<h4>Agregar nuevo campo</h4>
								<hr>
								<div class="row">
									<div class="col-md-6">
										{{ Form::bsText('etiqueta',null,'Etiqueta', [ 'required' => 'required']) }}
									</div>
									<div class="col-md-6">
										{{ Form::bsSelect('tipo',null,'Tipo',[ 'Texto' => 'Texto', 'Fecha' => 'Fecha'],[]) }}
									</div>

									{{ Form::hidden('url_id',Input::get('id'))}}
									{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
								</div>
							{{ Form::close() }}
							<div class="col-md-2" align="center">
								<div class="row" style="padding:5px;">
									<button class="btn btn-xs btn-primary" id="btn_agregar"><i class="fa fa-check"></i> Agregar</button>
					            </div>
			        		</div>
						</div>
					</div>
				</div>
			</div>
			<br/><br/>

			<?php

				$json = '{"0":{
							"etiqueta":"Tiene perros",
							"tipo":"select"
						},
						"1":{
							"etiqueta":"Mensaje Dcto. Pronto Pago",
							"tipo":"text"
						}}';

				//print_r( $lista_campos );
			?>
		</div>
	</div>
	<br/><br/>

	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$('#btn_agregar').click(function(event){
				event.preventDefault();
				if ( valida_campos_requeridos() ) 
				{
					var etiqueta = $('#etiqueta').val();
					var tipo = $('#tipo').val();
					var btn_eliminar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
					var fila = '<tr><td>'+etiqueta+'</td><td>'+tipo+'</td><td>'+btn_eliminar+'</td></tr>';
					$('#tabla_lista_campos').find('tbody:last').append( fila );
					$('#etiqueta').val('');
					$('#etiqueta').focus();
				}
					
			});

			$(document).on('click', '.btn_eliminar', function() 
			{
				var fila = $(this).closest("tr");
				fila.remove();
			});

			function valida_campos_requeridos()
			{
				var control = true;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = false;
					  alert('Este campo es requerido.');
					  return control;
					}else{
					  control = true;
					}
				});
				return control;
			}
		});

		
	</script>
@endsection