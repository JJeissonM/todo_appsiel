
<hr>
	
<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>Ingreso de calificaciones</h4>
	    <hr>
		{{ Form::open( [ 'url' => 'calificaciones/almacenar_calificacion', 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'formulario'] ) }}
					
			{{ Form::hidden('escala_min', $escala_min_max[0], ['id' =>'escala_min']) }}
			{{ Form::hidden('escala_max', $escala_min_max[1], ['id' =>'escala_max']) }}

			{{ Form::hidden('id_colegio', $id_colegio, ['id' =>'id_colegio']) }}
			{{ Form::hidden('creado_por', $creado_por, ['id' =>'creado_por']) }}
			{{ Form::hidden('modificado_por', $modificado_por, ['id' =>'modificado_por']) }}
			{{ Form::hidden('id_periodo', $periodo->id, ['id' =>'id_periodo']) }}
			{{ Form::hidden('curso_id', $curso->id, ['id' =>'curso_id']) }}
			{{ Form::hidden('anio', $anio, ['id' =>'anio']) }}
			{{ Form::hidden('id_asignatura', $datos_asignatura->id, ['id' =>'id_asignatura']) }}
			{{ Form::hidden('cantidad_estudiantes', $cantidad_estudiantes, ['id' =>'cantidad_estudiantes']) }}			

			{{ Form::hidden('codigo_matricula',null,['id'=>'codigo_matricula']) }}
			{{ Form::hidden('id_estudiante',null,['id'=>'id_estudiante']) }}

			{{ Form::hidden('id_calificacion_aux',null,['id'=>'id_calificacion_aux']) }}
			@for ($c=1; $c < 16; $c++)
				{{ Form::hidden('C'.$c,null,['id'=>'C'.$c]) }}
			@endfor

			{{ Form::hidden('calificacion',null,['id'=>'calificacion']) }}
			{{ Form::hidden('id_calificacion',null,['id'=>'id_calificacion']) }}

			{{ Form::hidden('id_app',Input::get('id')) }}		
			{{ Form::hidden('return', $ruta ) }}

		{{Form::close()}}

		<div class="row">
			<div class="col-sm-12">
				<b>Año:</b><code>{{ $anio }}</code>
				<b>Periodo:</b>	<code>{{ $periodo->descripcion }}</code>
				<b>Curso:</b><code>{{ $curso->descripcion }}</code>
				<b>Asignatura:</b><code>{{ $datos_asignatura->descripcion }}</code>

			</div>							
		</div>

		<div class="row">
			<div class="col-sm-12">
				<h4><i class="fa fa-info-circle"> &nbsp; </i>Use las flechas de dirección y tabular para desplazarse: &nbsp;<i class="fa fa-arrow-down"></i>&nbsp;<i class="fa fa-arrow-up"></i>&nbsp;<b >TAB </b></h4>
			</div>
			</br></br>							
		</div>

			<p style="color: gray; text-align: right;" id="mensaje_formulario">
				
				<spam id="mensaje_inicial">
				&nbsp;</spam>
				
				<spam id="mensaje_sin_guardar" style="background-color:#eaabab; display: none;">
				Sin guardar</spam>
				
				<spam id="mensaje_guardando" style="background-color:#faee8e; display: none;">
				Guardando...</spam>
				
				<spam id="mensaje_guardadas" style="background-color: #b1e6b2;">
				Calificaciones guardadas</spam>
			</p>

		<div class="row">
			<div class="col-sm-12">

				@yield('tabla')
				
			</div>
		</div>

		<div style="text-align: center; width: 100%;">
			<input class="btn btn-primary btn-xs" id="bs_boton_guardar" type="submit" value="Guardar" disabled="disabled">

			<a href="{{ url()->previous() }}" class="btn btn-danger btn-xs">Volver</a>

		</div>

	</div>
</div>

@include('components.design.ventana_modal', [ 'titulo' => 'Ingreso/Actualización encabezados de calificaciones', 'texto_mensaje' => 'Registro actualizado correctamente'])