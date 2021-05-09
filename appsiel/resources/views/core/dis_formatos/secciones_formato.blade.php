@extends('layouts.principal')

@section('content')
	<h3><i class="fa fa-file-text-o"></i> Diseñador de formatos</h3>
	<hr>

	@include('gestion_documental.submenu')

	{{ Form::bsBtnVolver('dis_formatos/vista/formatos') }}

	<div class="col-sm-offset-2 col-sm-8">
		<div class="panel panel-success">
			<div class="panel-heading" align="center">
				Asignación de secciones
				<br/>
				<h3>Formato {{$formato->descripcion}}</h3>
			</div>
			
			<div class="panel-body">
				{{ Form::open(['url'=>'dis_formatos/guardar_asignacion','id'=>'formulario']) }}

					{{ Form::hidden('id_formato',$formato->id) }}

					<div class='form-group'>
				        {{ Form::label('id_seccion', 'Sección') }}
						<select name='id_seccion' class="form-control" id='id_seccion'>
						<option value="9999"></option>
				        @foreach ($secciones_no_formato as $fila)
							<option value="{{$fila->id}}">{{$fila->descripcion}}</option>
				        @endforeach
						</select>
				    </div>

				    {{ Form::bsText('orden',null,'Orden',[]) }}
					
					{{ Form::submit('Guardar',['class'=>'btn btn-success btn-sm','id'=>'btn_guardar']) }}


					<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
					  <div class="modal-dialog modal-dialog-centered" role="document" align="center">
					  		Espere por favor... <br/>
					        <img src="{{ asset('assets/img/spinning-wheel.gif') }}" width="40px" height="40px">
					  </div>
					</div>
					
				{{ Form::close() }}
				<br/>
				<div class="alert alert-danger" id="div_error" style="display: none;">
					<em> {!! session('mensaje_error') !!}</em>
				</div>

				<div class='alert alert-success' id="div_success" style="display: none;">
			        <em> Asignación agregada correctamente. </em>
			    </div>

				<div id="listado">
						<h3>Secciones asociadas</h3>
						<div class="row">
							<div class="col col-md-12">
								<table class="table table-bordered table-striped">
									{{ Form::bsTableHeader(['Orden','Nombre','Presentación','Contenido','Acción']) }}
									<tbody>
										@foreach($secciones_formato as $fila)
											<?php
												$seccion = App\DifoSeccion::find($fila->id_seccion);
											?> 
											<tr>
												<td class="text-center">{{$fila->orden}}</td>
												<td>{{$seccion->descripcion}}</td>
												<td>{{$seccion->presentacion}}</td>
												<td>{{$seccion->contenido}}</td>
												<td>
													<form action="{{ url('dis_formatos/eliminar_asignacion') }}" class="formulario" method="POST">
														{{ csrf_field() }}

														{{ Form::hidden('id',$fila->id)}}
														{{ Form::hidden('id_formato',$formato->id)}}												
														<button type="submit" class="btn btn-danger btn-xs btn-detail"><i class="fa fa-btn fa-trash"></i></button>
													</form>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
				</div>

				<div id="listado2" style="display: none;">
					
				</div>
			</div>
		</div>
	</div>	
@endsection

@section('scripts')
@endsection