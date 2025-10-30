@extends('layouts.principal')

@section('content')

<?php 
    //dd($miga_pan);
?>
	{{ Form::bsMigaPan($miga_pan) }}
    <hr>

@include('layouts.mensajes')
	
<div class="container-fluid">
	<div class="marco_formulario">
		<div class="container-fluid">
		    <h4>Registros de Turnos</h4>
		    <hr>
			{{Form::open(array( 'route' => array('nom_turnos_registros.store'),'method'=>'POST','class'=>'form-horizontal','id'=>'formulario'))}}
				<div class="row">
                    
					<div class="col-sm-2">
                        &nbsp;
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
                        &nbsp;
                    </div>						
				</div>
				<div class="row">
                    
					<div class="col-sm-2">
                        &nbsp;
                    </div>

					<div class="col-sm-8">

						<table class="table table-responsive">
							<?php
							?>
							<thead>
								<tr>
									<th>Empleado</th>
									<th>Turno</th>
									<th>Anotación</th>
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
                                            {{ Form::select('tipo_turno_id[]', $tipos_turnos, $empleado->tipo_turno_id, [ 'class' => 'combobox' ] ) }}
										</td>

										<td>
                                            {{ Form::textarea('anotacion[]', $empleado->anotacion, [ 'rows' => '3' ] ) }}
										</td>

										<td>
                                            {{ $empleado->estado_turno }}
										</td>
			                        </tr>
								@endforeach
							</tbody>
						</table>
					</div>
                    
					<div class="col-sm-2">
                        &nbsp;
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