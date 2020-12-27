@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
<style>
table th {
    padding: 15px;
    text-align: center;
	border-bottom:solid 2px;
	background-color: #E5E4E3;
}
table td {
    padding: 2px;
}
</style>

<hr>

@include('layouts.mensajes')
	
<div class="container-fluid">
	<div class="marco_formulario">
		<div class="container-fluid">
		    <h4>Ingreso de registros</h4>
		    <hr>
			{{Form::open(array('route'=>array('nom_registros_documentos.store'),'method'=>'POST','class'=>'form-horizontal','id'=>'formulario'))}}
				<div class="row">
					<div class="col-sm-12">
						<p>
							<b>Documento de nómina:</b><code>{{ $documento->descripcion }}</code>
							<b>Concepto:</b>	<code>{{ $concepto->descripcion }}</code>
							<b>Porc. del Básico:</b>	<code>{{ $concepto->porcentaje_sobre_basico }}%</code>
							
							{{ Form::hidden('nom_doc_encabezado_id', $documento->id, ['id' =>'nom_doc_encabezado_id']) }}
							
							{{ Form::hidden('nom_concepto_id', $concepto->id, ['id' =>'nom_concepto_id']) }}

							{{ Form::hidden('cantidad_empleados', $cantidad_empleados, ['id' =>'cantidad_empleados']) }}
						</p>
					</div>							
				</div>
				<div class="row">
					<div class="col-sm-12">

						<table class="table table-responsive" id="myTable2">
							<?php 
								$lbl_encabezado = 'Valor concepto';
								if ( (float)$concepto->porcentaje_sobre_basico != 0 )
								{
									$lbl_encabezado = 'Cantidad horas';
								}
							?>
							<thead>
								<tr>
									<th>Empleado</th>
									<th>{{ $lbl_encabezado }}</th>
								</tr>
							</thead>
							<tbody>
								@foreach($empleados as $empleado)
									<tr> 
										<td style="font-size:12px">
											<b>{{ $empleado->tercero->descripcion }}</b>
											
											{{ Form::hidden('core_tercero_id[]', $empleado->core_tercero_id, []) }}

										</td>

										<td>
											@if ( (float)$concepto->porcentaje_sobre_basico != 0 )
												<input type="text" name="cantidad_horas[]" class="form-control" placeholder="Cantidad horas">
											@else
												<input type="text" name="valor[]" class="form-control" placeholder="Valor">
											@endif
										</td>
			                        </tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>	

				<div style="text-align: center; width: 100%;">
					{{ Form::bsButtonsForm( url()->previous() ) }}
				</div>

				{{ Form::hidden('app_id',Input::get('id')) }}
				{{ Form::hidden('modelo_id',Input::get('id_modelo')) }}

			{{Form::close()}}
		</div>					
	</div>
</div> 

@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#formulario').submit();
			});

			$('#myTable2').DataTable({
				dom: 'Bfrtip',
				"paging": false,
				"searching": false,
				buttons: [],
				order: [
					[0, 'asc']
				]
			});

		});
	</script>

@endsection