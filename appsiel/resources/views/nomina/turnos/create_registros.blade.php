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
		    <h4>Ingreso de registros de Turnos</h4>
		    <hr>
			{{Form::open(array( 'route' => array('nom_turnos_registros.store'),'method'=>'POST','class'=>'form-horizontal','id'=>'formulario'))}}
				<div class="row">
                    
					<div class="col-sm-2">
                        &nbsp;
                    </div>

					<div class="col-sm-8">
						<p>
							{{ Form::date('fecha', $fecha, ['id' =>'fecha']) }}
						</p>
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

						<table class="table table-responsive" id="myTable2">
							<?php
							?>
							<thead>
								<tr>
									<th>Empleado</th>
									<th>Turno</th>
									<th>Anotación</th>
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