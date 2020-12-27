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
	}
</style>

<hr>

@include('layouts.mensajes')
	
<div class="container-fluid">
	<div class="marco_formulario">
	    <h4 style="text-align: center;">
	    	Ingreso de Anotaciones - Preinforme Académico 
			<br> 
			Año lectivo: {{ $periodo_lectivo->descripcion }}
	    </h4>
	    <hr>
		{{ Form::open( [ 'url' => 'cali_preinforme_academico_almacenar_anotacion', 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'formulario'] ) }}
					
			{{ Form::hidden('id_colegio', $id_colegio, ['id' =>'id_colegio']) }}
			{{ Form::hidden('creado_por', $creado_por, ['id' =>'creado_por']) }}
			{{ Form::hidden('modificado_por', $modificado_por, ['id' =>'modificado_por']) }}
			{{ Form::hidden('id_periodo', $periodo->id, ['id' =>'id_periodo']) }}
			{{ Form::hidden('curso_id', $curso->id, ['id' =>'curso_id']) }}
			{{ Form::hidden('id_asignatura', $datos_asignatura->id, ['id' =>'id_asignatura']) }}
			{{ Form::hidden('cantidad_estudiantes', $cantidad_estudiantes, ['id' =>'cantidad_estudiantes']) }}			

			{{ Form::hidden('codigo_matricula',null,['id'=>'codigo_matricula']) }}
			{{ Form::hidden('id_estudiante',null,['id'=>'id_estudiante']) }}

			{{ Form::hidden('anotacion',null,['id'=>'anotacion']) }}
			{{ Form::hidden('id_anotacion',null,['id'=>'id_anotacion']) }}

			{{ Form::hidden('id_app',Input::get('id')) }}

		{{Form::close()}}

		<div class="row">
			<div class="col-sm-12">
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
				
				<spam id="mensaje_guardando" style="background-color:#a3e7fe; display: none;">
				Guardando...</spam>
				
				<spam id="mensaje_guardadas" style="background-color: #b1e6b2;">
				anotaciones guardadas</spam>
			</p>

		<div class="row">
			<div class="col-sm-12">

				<div class="table-responsive">
					<table class="table table-striped" id="tabla_registros">
						<thead>
							<tr>
								<th>Estudiantes</th>
								<th>
									Anotaciones
								</th>
							</tr>
						</thead>
						<tbody>
							<?php $linea=1; ?>

							@for($k=0;$k<$cantidad_estudiantes;$k++)		

								<tr data-codigo_matricula="{{ $vec_estudiantes[$k]['codigo_matricula'] }}"  data-id_estudiante="{{ $vec_estudiantes[$k]['id_estudiante'] }}"  data-id_anotacion="{{ $vec_estudiantes[$k]['id_anotacion'] }}"  data-anotacion_texto="{{ $vec_estudiantes[$k]['anotacion'] }}"  >

									<td width="350px" style="font-size:12px">
										<b>{{ $vec_estudiantes[$k]['nombre'] }}</b>
									</td>

									<td>
										<textarea name="anotacion_texto[]" id="anotacion_texto{{$linea}}" class="form-control">{{ $vec_estudiantes[$k]['anotacion'] }}</textarea>
									</td>
								</tr>
								<?php $linea++; ?>
							@endfor
							
						</tbody>
					</table>
				</div>
				
			</div>
		</div>

		<div style="text-align: center; width: 100%;">
			<input class="btn btn-primary btn-xs" id="bs_boton_guardar" type="submit" value="Guardar" disabled="disabled">

			<button class="btn btn-danger btn-xs" id="bs_boton_volver">Volver</button>

		</div>

	</div>
</div>

@endsection

@section('scripts')
	
	<script language="javascript">

		$( document ).ready(function() {

			checkCookie();

			// 16 = Shift
	   		// 9 = Tab
	   		// 8 = Backspace
	   		var teclas_especiales = [9,16];

	   		var guardando = false;

	  		// Cuando se presiona una caja de texto
			$(document).on('keyup',"textarea", function(e) 
			{
				if ( $.inArray(e.keyCode,teclas_especiales) < 0 )
		   		{	// Si NO se presionan teclas especiales

		   			// Cuando cambie el valor de una celda, se cambian los mensajes
		   			$('#mensaje_inicial').hide();
					$('#mensaje_guardadas').hide();
					$('#mensaje_sin_guardar').show();
					$('#bs_boton_guardar').prop('disabled', false);

		   		}
			});

			$('#bs_boton_guardar').click(function(){
				guardar_anotaciones();
			});

			$('#bs_boton_volver').click(function(){
				document.location.href = "{!! url()->previous() !!}";
			});

			window.guardar_anotaciones=function(){

				// DESCOMENTAR LA LINEA DE ABAJO EN PRODUCCION
				$('#bs_boton_guardar').prop('disabled', true);
				$('#bs_boton_volver').prop('disabled', true);

				
				guardando = true;

				var item;

				$('#mensaje_inicial').hide();
				$('#mensaje_sin_guardar').hide();
				$('#mensaje_guardando').show();

				var linea = 1;
				$('#tabla_registros > tbody > tr').each( function(i,item)
				{
					$('#codigo_matricula').val( $(item).attr('data-codigo_matricula') );
					$('#id_estudiante').val( $(item).attr('data-id_estudiante') );

					$('#id_anotacion').val( $(item).attr('data-id_anotacion') );
					$('#anotacion').val( $('#anotacion_texto'+linea).val() );

					var url = $("#formulario").attr('action');
					var data = $("#formulario").serialize();

					var search_params = new URLSearchParams(data); 

					search_params.set('_token', '{{ csrf_token() }}');

					data = search_params.toString();

					/**/
					$.post(url, data, function( respuesta ){

						$(item).attr('data-id_anotacion',respuesta[0]);
						$(item).attr('data-anotacion_texto',respuesta[1]);
					});

					linea++;
				});

				$('#mensaje_guardando').hide();
				$('#bs_boton_volver').prop('disabled', false);

				$('#mensaje_guardadas').show();
				
				guardando = false;
			};


			function setCookie(cname, cvalue, exdays) {
			  var d = new Date();
			  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			  var expires = "expires="+d.toUTCString();
			  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
			}

			function getCookie(cname) {
			  var name = cname + "=";
			  var ca = document.cookie.split(';');
			  for(var i = 0; i < ca.length; i++) {
			    var c = ca[i];
			    while (c.charAt(0) == ' ') {
			      c = c.substring(1);
			    }
			    if (c.indexOf(name) == 0) {
			      return c.substring(name.length, c.length);
			    }
			  }
			  return "";
			}

			function checkCookie() {
			  var mostrar_ayuda = getCookie("mostrar_ayuda_anotaciones_form");

			  //alert(mostrar_ayuda);

			  if (mostrar_ayuda == "true" || mostrar_ayuda == "") {
			    
			    $("#myModal").modal(
		        	{keyboard: 'true'}
		        );

		        $(".modal-title").html('Ayuda');
		        $(".btn_edit_modal").hide();
		        $(".btn_save_modal").hide();

		        /* <li class="list-group-item">Las anotaciones se almacenan automáticamente cada diez (10) segundos.</li> */
		        $("#contenido_modal").html('<div class="well well-lg"><ul class="list-group"><li class="list-group-item">Se pueden guardar las anotaciones en cualquier momento presionando el botón guardar y seguir ingresando información.</li>  <li class="list-group-item">Verifique que antes de salir de la página se muestre el mensaje <spam id="mensaje_guardadas" style="background-color: #b1e6b2;">anotaciones guardadas</spam></li></ul> <div class="checkbox">  <label><input type="checkbox" name="mostrar_ayuda_anotaciones_form" id="mostrar_ayuda_anotaciones_form" value="true">No volver a mostrar este mensaje.</label> </div></div>');
		        
		        setCookie("mostrar_ayuda_anotaciones_form", true, 365);

		        $(document).on('click','#mostrar_ayuda_anotaciones_form',function(){
		        	if( $(this).val() == "true" ){
		        		$(this).val( "false" );
		        		setCookie("mostrar_ayuda_anotaciones_form", "false", 365);
		        	}else{
		        		$(this).val( "true" );
		        		setCookie("mostrar_ayuda_anotaciones_form", "true", 365);
		        	}
		        });
			  }
			}

		});		
		
	</script>
@endsection