<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Reg Excel</title>
	<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
	
	<link rel="stylesheet" href="{{ asset('css/editablegrid/editablegrid.css') }}" type="text/css" media="screen">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/micss.css') }}">
</head>
<body>
	@include('layouts.menu_principal')
	<div class="main">

	<h2>
		<i class="fa fa-child"></i> Gestor de Estudiantes
	</h2>
	<hr>


		<section class="main row">
			
			<div class="container">
			<div>
			{{ Form::open (['url' => '/matriculas/estudiantes/importar_excel/guardar', 'method' => 'POST', 'class' => 'form-horizontal']) }}

			{!! csrf_field() !!}
			<br>
			<a href="{{ url('/matriculas/estudiantes/importar_excel') }}" class="btn btn-lg btn-primary btn-danger">Cancelar</a>
			{{ Form::submit('Guardar', ['class' => 'btn btn-lg btn-primary pull-right', 'id' => 'request'])}}
			<!-- <a href="#" class="btn btn-primary pull-right">Procesar</a> --><br><br>
			</div>

			<div class="alert alert-info">
			  <strong>¡Indicaciones!</strong> 
			  	<ul class="list-group">
				  <li class="list-group-item">La primera fila del archivo de excel debe tener los nombres de los campos a cargar y estos deben se iguales a los que se muestran abajo. (nombres, apellido1, apellido2, etc.)</li>
				  <li class="list-group-item">Revise la información antes de cargarla. Este proceso no se podrá revertir automáticamente. Luego presione el botón Guardar para almacenar la información.</li>
				  <li class="list-group-item">Los errores se presentan resaltados. Todos los campos son obligatorios para cargar el archivo. Corrija el error aquí mismo y presione Guardar nuevamente.</li>
				</ul>
			</div>

			<div class="table-responsive">
				<table class="table table-striped">
		            <thead>
		                <tr>
		                	<th>#</th>
							<th>nombres</th>
							<th>apellido1</th>
							<th>apellido2</th>
							<th>doc_identidad</th>
							<th>genero</th>
							<th>direccion1</th>
							<th>barrio</th>
							<th>telefono1</th>
							<th>fecha_nacimiento</th>
							<th>ciudad_nacimiento</th>
							<th>mama</th>
							<th>ocupacion_mama</th>
							<th>telefono_mama</th>
							<th>email_mama</th>
							<th>papa</th>
							<th>ocupacion_papa</th>
							<th>telefono_papa</th>
							<th>email_papa</th>

		                </tr>
		            </thead>
		            <tbody>
		            	@foreach($data as $key => $value)
						<tr>
							<td>{!! $i = $value['id'] + 1 !!}</td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="nombres[]" value="{{ $value['nombres'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['nombres'])) ? $errors[$value['id']]['nombres'] : '' }}" class="{{ (isset($errors[$value['id']]['nombres'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="apellido1[]" value="{{ $value['apellido1'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['apellido1'])) ? $errors[$value['id']]['apellido1'] : '' }}" class="{{ (isset($errors[$value['id']]['apellido1'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="apellido2[]" value="{{ $value['apellido2'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['apellido2'])) ? $errors[$value['id']]['apellido2'] : '' }}" class="{{ (isset($errors[$value['id']]['apellido2'])) ? 'form-control has-error' : '' }}"></td>
							
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="doc_identidad[]" value="{{ $value['doc_identidad'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['doc_identidad'])) ? $errors[$value['id']]['doc_identidad'] : '' }}" class="{{ (isset($errors[$value['id']]['doc_identidad'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="genero[]" value="{{ $value['genero'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['genero'])) ? $errors[$value['id']]['genero'] : '' }}" class="{{ (isset($errors[$value['id']]['genero'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="direccion1[]" value="{{ $value['direccion1'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['direccion1'])) ? $errors[$value['id']]['direccion1'] : '' }}" class="{{ (isset($errors[$value['id']]['direccion1'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="barrio[]" value="{{ $value['barrio'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['barrio'])) ? $errors[$value['id']]['barrio'] : '' }}" class="{{ (isset($errors[$value['id']]['barrio'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="telefono1[]" value="{{ $value['telefono1'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['telefono1'])) ? $errors[$value['id']]['telefono1'] : '' }}" class="{{ (isset($errors[$value['id']]['telefono1'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="fecha_nacimiento[]" value="{{ $value['fecha_nacimiento'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['fecha_nacimiento'])) ? $errors[$value['id']]['fecha_nacimiento'] : '' }}" class="{{ (isset($errors[$value['id']]['fecha_nacimiento'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="ciudad_nacimiento[]" value="{{ $value['ciudad_nacimiento'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['ciudad_nacimiento'])) ? $errors[$value['id']]['ciudad_nacimiento'] : '' }}" class="{{ (isset($errors[$value['id']]['ciudad_nacimiento'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="mama[]" value="{{ $value['mama'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['mama'])) ? $errors[$value['id']]['mama'] : '' }}" class="{{ (isset($errors[$value['id']]['mama'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="ocupacion_mama[]" value="{{ $value['ocupacion_mama'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['ocupacion_mama'])) ? $errors[$value['id']]['ocupacion_mama'] : '' }}" class="{{ (isset($errors[$value['id']]['ocupacion_mama'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="telefono_mama[]" value="{{ $value['telefono_mama'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['telefono_mama'])) ? $errors[$value['id']]['telefono_mama'] : '' }}" class="{{ (isset($errors[$value['id']]['telefono_mama'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="email_mama[]" value="{{ $value['email_mama'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['email_mama'])) ? $errors[$value['id']]['email_mama'] : '' }}" class="{{ (isset($errors[$value['id']]['email_mama'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="papa[]" value="{{ $value['papa'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['papa'])) ? $errors[$value['id']]['papa'] : '' }}" class="{{ (isset($errors[$value['id']]['papa'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="ocupacion_papa[]" value="{{ $value['ocupacion_papa'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['ocupacion_papa'])) ? $errors[$value['id']]['ocupacion_papa'] : '' }}" class="{{ (isset($errors[$value['id']]['ocupacion_papa'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="telefono_papa[]" value="{{ $value['telefono_papa'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['telefono_papa'])) ? $errors[$value['id']]['telefono_papa'] : '' }}" class="{{ (isset($errors[$value['id']]['telefono_papa'])) ? 'form-control has-error' : '' }}"></td>
							<td><input type="text" size="10" id="{{ $value['id'] }}" name="email_papa[]" value="{{ $value['email_papa'] }}" data-toggle="popover" data-placement="top" data-trigger="hover" data-content="{{ (isset($errors[$value['id']]['email_papa'])) ? $errors[$value['id']]['email_papa'] : '' }}" class="{{ (isset($errors[$value['id']]['email_papa'])) ? 'form-control has-error' : '' }}"></td>
						</tr>

						@endforeach

		                {{ Form::close() }}
			        </tbody>
		        </table>
		        </div>
			</div>	
		</section>
	</div>

		<!-- <script src="{{ asset('js/editablegrid/editablegrid.js') }}"></script> -->
	<!-- [DO NOT DEPLOY] --> <!-- <script src="{{ asset('js/editablegrid/editablegrid_renderers.js') }}" ></script> -->
	<!-- [DO NOT DEPLOY] --> <!-- <script src="{{ asset('js/editablegrid/editablegrid_editors.js') }}" ></script> -->
	<!-- [DO NOT DEPLOY] --> <!-- <script src="{{ asset('js/editablegrid/editablegrid_validators.js') }}" ></script> -->
	<!-- [DO NOT DEPLOY] --> <!-- <script src="{{ asset('js/editablegrid/editablegrid_utils.js') }}" ></script> -->
	<!-- [DO NOT DEPLOY] --> <!-- <script src="{{ asset('js/editablegrid/editablegrid_charts.js') }}" ></script> -->
	<!-- <script type="text/javascript" src="{{ asset('js/tableEdit.js') }}"></script> -->	<!-- tabla editable-->
	<script type="text/javascript" src="{{ asset('js/jquery.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
		<!-- mi ajax -->
	<script type="text/javascript" src="{{ asset('js/mijs.js') }} "></script>

</body>
</html>