@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>
	
<style>
	table th {
	    padding: 15px;
	    text-align: center;
		border-bottom:solid 2px;
		background-color: #E5E4E3;
	}
	table td {
	    padding: 2px;
	}z
</style>

<hr>

@include('layouts.mensajes')
	
<div class="container-fluid">
	<div class="marco_formulario">

		<div class="container-fluid">
			<a class="btn btn-success btn-md" href="{{url('/index_procesos/matriculas.procesos.consolidado_evaluacion_por_aspectos?id=' . Input::get('id') )}}" title="Generar consolidados"><i class="fa fa-users"></i> Generar consolidados </a>
		</div>

	    <h4 style="text-align: center;">
	    	Ingreso de valoraciones de estudiantes 
			<br> 
			Periodo de evaluacion: {{ $semana_calendario->descripcion }}
	    </h4>
	    <hr>

		<div class="row">
			<div class="col-md-3">
				{{ Form::bsFecha( 'fecha_valoracion_aux' , $fecha_valoracion, 'Fecha', [], [] ) }}
			</div>	
			<div class="col-md-4">
				{{ Form::bsSelect( 'curso_id_aux' , $curso->id, 'Curso', $cursos_profesor, [] ) }}
			</div>	
			<div class="col-md-3">
				{{ Form::bsSelect( 'id_asignatura_aux' , $datos_asignatura->id, 'Asignatura', $asignaturas_curso, [] ) }}
			</div>
			<div class="col-md-2">
				<a href="#" class="btn btn-primary btn-sm" id="btn_actualizar"> Actualizar </a>
			</div>
		</div>

		<br><br>

		<div class="row">
			<div class="col-sm-12">
				<div class="table-responsive">
					{{ Form::open( [ 'url' => 'sga_observador_evaluacion_por_aspectos_almacenar_valoracion', 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'form_create'] ) }}

						{{ Form::hidden('fecha_valoracion', $fecha_valoracion, []) }}
						{{ Form::hidden('id_colegio', $id_colegio, []) }}
						{{ Form::hidden('creado_por', $creado_por, []) }}
						{{ Form::hidden('modificado_por', $modificado_por, []) }}
						{{ Form::hidden('curso_id', $curso->id, []) }}
						{{ Form::hidden('id_asignatura', $datos_asignatura->id, []) }}
						{{ Form::hidden('cantidad_estudiantes', $cantidad_estudiantes, []) }}
						{{ Form::hidden('cantidad_items_aspectos', $cantidad_items_aspectos, []) }}	
						{{ Form::hidden('periodo_lectivo_id', $periodo_lectivo->id, [ 'id' => 'periodo_lectivo_id' ]) }}	

						{{ Form::hidden('url_id',Input::get('id')) }}

						<table class="table table-striped" id="tabla_registros">
							<thead>
								<tr>
									<th>&nbsp;</th>
									@foreach( $tipos_aspectos AS $tipo_aspecto )
										<?php $cant_items_del_tipo = $items_aspectos->where('id_tipo_aspecto', $tipo_aspecto->id )->count() ?>
										<th colspan="{{$cant_items_del_tipo}}" align="center">{{$tipo_aspecto->descripcion}}</th>
									@endforeach
								</tr>
								<tr>
									<th>Estudiantes</th>
									@foreach( $items_aspectos AS $item_aspecto )
										<th class="celda_C{{$item_aspecto->id}}" align="center" title="{{ $item_aspecto->descripcion }}">
											{{$item_aspecto->descripcion}}
										</th>
									@endforeach
								</tr>
							</thead>
							<tbody>
								<?php 

									$linea=1;
								?>

								@for( $k = 0; $k < $cantidad_estudiantes; $k++)

									<tr valign="{{$linea}}" title="{{$vec_estudiantes[$k]['nombre']}}">									

										<td width="250px" style="font-size:12px">
											<b> {{$linea}} {{ $vec_estudiantes[$k]['nombre'] }}</b>
											{{ Form::hidden('codigo_matricula[]',$vec_estudiantes[$k]['codigo_matricula'],[]) }}
											{{ Form::hidden('id_estudiante[]',$vec_estudiantes[$k]['id_estudiante'],[]) }}
										</td>				
										
										@for ( $c=1; $c <= $cantidad_items_aspectos; $c++ )
											<td class="celda_C{{$c}}">
												{{ Form::select('valores_item_'.$c.'[]', $convenciones, $vec_estudiantes[$k]['valoraciones_aspectos']['valores_item_'.$c], array_merge( [ 'id' => 'valores_item_'.$c, 'title' => $vec_estudiantes[$k]['fechas_valoraciones_aspectos']['valores_item_'.$c] ], [] )) }}
												{{ Form::hidden('fechas_valores_item_'.$c.'[]',$vec_estudiantes[$k]['fechas_valoraciones_aspectos']['valores_item_'.$c],[]) }}
											</td>
										@endfor
									</tr>
									<?php $linea++; ?>
								@endfor
								
							</tbody>
						</table>
					{{Form::close()}}
				</div>				
			</div>
		</div>

		<div style="text-align: center; width: 100%;">
			<button class="btn btn-primary btn-xs" id="bs_boton_guardar">Guardar</button>
			<a href="{{ URL::previous() }}" class="btn btn-danger btn-xs" id="bs_boton_volver"> Volver </a>
		</div>

	</div>
</div>


@endsection

@section('scripts')


	<script language="javascript">

		$( document ).ready(function() {
			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				$('#form_create').submit();
			});

			$('td').hover(function(){
				var fila_encabezado  = $('table thead tr').eq( 1 ); // La segunda fila del encabezado
				//console.log( fila_encabezado.find('th').eq(2).html() );
				//var celda_encabezado = $('table thead tr[1] th').eq( $(this).index() );
				var celda_encabezado = fila_encabezado.find('th').eq( $(this).index() );
				var etiqueta_mostrar = $(this).parent('tr').attr('title') + ": " + celda_encabezado.attr('title');
				$(this).attr( 'title', etiqueta_mostrar );
			});



			$('#fecha_valoracion_aux').change(function(event){
				cambiar_enlace_boton();
			});

			$('#curso_id_aux').change(function(event){
				cambiar_enlace_boton();
			});

			$('#id_asignatura_aux').change(function(event){
				cambiar_enlace_boton();
			});

			function cambiar_enlace_boton()
			{

				if ( $('#curso_id_aux').val() == '' )
				{
					return false;
				}

				if ( $('#id_asignatura_aux').val() == '' )
				{
					return false;
				}

				var url = $('#curso_id_aux').val() + "/" + $('#id_asignatura_aux').val() + "/" + $('#fecha_valoracion_aux').val() + "?id={{Input::get('id')}}";

				$('#btn_actualizar').attr( 'href', "{{ url('/sga_observador_evaluacion_por_aspectos_ingresar_valoracion')}}" + "/" + url );				
			}

			$('#curso_id_aux').on('change',function()
			{
				// Debe haber Select Asignatura
				$('#id_asignatura_aux').html('<option value=""></option>');

				if ( $(this).val() == '') { return false; }

	    		$('#div_cargando').show();

				var url = "{{ url('calificaciones_opciones_select_asignaturas_del_curso') }}" + "/" + $('#curso_id_aux').val() + "/null" + "/" + $('#periodo_lectivo_id').val() + "/Activo";

				$.ajax({
		        	url: url,
		        	type: 'get',
		        	success: function(datos){

		        		$('#div_cargando').hide();
	    				
	    				$('#id_asignatura_aux').html( datos );
						$('#id_asignatura_aux').focus();
			        }
			    });					
			});

		});
		
	</script>
@endsection