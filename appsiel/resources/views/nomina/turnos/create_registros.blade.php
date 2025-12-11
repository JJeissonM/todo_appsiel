@extends('layouts.principal')

@section('content')

@php
$fechaAnterior = \Carbon\Carbon::parse($fecha)->subDay()->format('Y-m-d');
$fechaSiguiente = \Carbon\Carbon::parse($fecha)->addDay()->format('Y-m-d');
@endphp

{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="container-fluid">
			<h4>Registros de Turnos</h4>
			<hr>
			{{Form::open(array( 'route' =>
			array('nom_turnos_registros.store'),'method'=>'POST','class'=>'form-horizontal','id'=>'formulario'))}}
			<div class="row">

				<div class="col-sm-2">
					<a class="btn btn-primary"
						href="{{ url('nom_turnos_registros/create') }}?id={{ Input::get('id') }}&id_modelo={{ Input::get('id_modelo') }}&fecha={{ $fechaAnterior }}">
						← Día anterior
					</a>
				</div>

				<div class="col-sm-8">
					<p>
						{{ Form::bsFecha('fecha', $fecha, 'Fecha', null, ['id' =>'fecha']) }}

						{{ Form::hidden('action', $action, ['id' =>'action']) }}
					</p>
					@if($action == 'edit')
					<div class="alert alert-warning">
						<strong>Nota:</strong> Ya existen registros de turnos en la fecha seleccionada.
						<ul>
							<li> Al presionar Guardar, los datos serán actualizados.</li>
							<li> Si se deja el Turno vacío, ese registro será Borrado.</li>
						</ul>
					</div>
					<div class="alert alert-warning">
						<strong>Nota2:</strong> Los turnos en estado "Liquidado" no serán actualizados.
					</div>
					@endif
				</div>

				<div class="col-sm-2">
					<a class="btn btn-primary"
						href="{{ url('nom_turnos_registros/create') }}?id={{ Input::get('id') }}&id_modelo={{ Input::get('id_modelo') }}&fecha={{ $fechaSiguiente }}">
						Día siguiente →
					</a>
				</div>
			</div>

			<div class="row">

				<div class="col-sm-12">
    				<div class="table-responsive" style="overflow-x: auto;">

						<table class="table table-responsive" style="width:100%;">
							<?php
								?>
							<thead>
								<tr>
									<th style="width: 15%;">Empleado</th>
									<th>Hora entrada 1</th>
									<th>Hora salida 1</th>
									<th>Hora entrada 2</th>
									<th>Hora salida 2</th>
									<th>Turno</th>
									<th style="width: 150px;">Anotación</th>
									<th>Estado</th>
								</tr>
							</thead>
							<tbody>
								@foreach($empleados as $empleado)
								<tr>
									<td style="font-size:12px">
										<b>{{ $empleado->tercero->descripcion }}</b>

										{{ Form::hidden('contrato_id[]', $empleado->id, []) }}

									</td>

									<td>
										{{ Form::time('checkin_time_1[]', $empleado->checkin_time_1, [ 'class' =>
										'form-control', 'style' => 'font-size:0.9em; width: 110px;' ] ) }}
									</td>
									<td>
										{{ Form::time('checkout_time_1[]', $empleado->checkout_time_1, [ 'class' =>
										'form-control', 'style' => 'font-size:0.9em; width: 110px;' ] ) }}
									</td>
									<td>
										{{ Form::time('checkin_time_2[]', $empleado->checkin_time_2, [ 'class' =>
										'form-control', 'style' => 'font-size:0.9em; width: 110px;' ] ) }}
									</td>
									<td>
										{{ Form::time('checkout_time_2[]', $empleado->checkout_time_2, [ 'class' =>
										'form-control', 'style' => 'font-size:0.9em; width: 110px;' ] ) }}
									</td>
                                    <td>
                                        {{ Form::select('tipo_turno_id[]', $empleado->tipos_turno_options ?? $tipos_turnos, $empleado->tipo_turno_id, [
                                        'class' => 'combobox' ] ) }}
                                    </td>

									<td>
										{{ Form::textarea('anotacion[]', $empleado->anotacion, [ 'rows' => '3', 'cols' => '10' ] ) }}
									</td>

									<td>
										{{ $empleado->estado_turno }}
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div style="text-align: center; width: 100%;">
				{{ Form::bsButtonsForm( url('/web?id=17&id_modelo=337') ) }}
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

			$('#fecha').on('change',function(event){
				event.preventDefault();

				$('#div_cargando').show();

                document.location.href = "{{ url( 'nom_turnos_registros/create' ) }}?id={{ Input::get('id') }}&id_modelo={{ Input::get('id_modelo') }}&fecha=" + $( this ).val();
			});

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
