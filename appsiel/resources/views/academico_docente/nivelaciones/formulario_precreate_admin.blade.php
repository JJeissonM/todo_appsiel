@extends('layouts.principal')

<?php 
    $periodos = App\Calificaciones\PeriodoActivo::opciones_campo_select();

    $cursos = App\Matriculas\Curso::opciones_campo_select();

    $estudiantes = App\Matriculas\Matricula::estudiantes_matriculados(null, null, null);

    $vec_estudiantes[''] = '';
    foreach ($estudiantes as $opcion) {
        $vec_estudiantes[$opcion->id] = $opcion->nombre_completo;
    }
?>
@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Ingreso de notas de Nivelaciones</h4>
		    <hr>

		    <div class="row" style="padding:5px;">
				<a class="btn btn-danger btn-sm" href="{{ url('web') . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') }}"><i class="fa fa-arrow-left"></i> Volver </a>
			</div>

		    <div class="row">
		    	<div class="col-md-6">
		    		<h5><b>Seleccionar datos</b></h5>
		    		<hr>
		    		{{ Form::open( [ 'url' => 'sga_notas_nivelaciones_cargar_estudiante', 'id' => 'form_consulta' ] ) }}

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('estudiante_id','','Seleccionar estudiante',$vec_estudiantes,['required'=>'required','class' => 'combobox']) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('periodo_id','','Seleccionar periodo',$periodos,['required'=>'required']) }}
							{{ Form::hidden('periodo_lectivo_id',0,['id'=>'periodo_lectivo_id']) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('curso_id','','Seleccionar Curso',$cursos,['required'=>'required']) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('asignatura_id','','Seleccionar Asignatura',[],['required'=>'required']) }}
						</div>

						<div class="row" style="padding:5px; text-align: center;">
							<button class="btn btn-primary btn-sm" id="btn_cargar"><i class="fa fa-arrow-right"></i> Cargar</button>
						</div>
						
					{{ Form::close() }}
		    	</div>

		    	<div class="col-md-6">
		    		<h5><b>Almacenar Nota</b></h5>
		    		<hr>
		    		{{ Form::Spin(48) }}
		    		<div id="resultado_consulta">
		    			
		    		</div>
		    	</div>
		    </div>
					

		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$('#periodo_id').on('change', function(event){
				$('#resultado_consulta').html( "" );
                $('#curso_id').val(0);
                $('#asignatura_id').html('<option value=""></option>');

                var url = "{{ url('get_id_periodo_lectivo_del_periodo') }}" + "/" + $(this).val();

                $.ajax({
                    url: url,
                    type: 'get',
                    success: function(datos){

                        $('#div_cargando').hide();
                        
                        $('#periodo_lectivo_id').val(datos);
                    }
                });	

			});

			$('#estudiante_id').on('change', function(event){
				$('#resultado_consulta').html( "" );
			});


			$('#btn_cargar').click(function(event){
				event.preventDefault();
				$('#resultado_consulta').html( "" );

				if( !validar_requeridos() )
				{
					return false;
				}

				$('#div_spin').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();

				// Enviar formulario vía POST
				$.post(url,datos,function(respuesta){
					$('#div_spin').hide();
					$('#resultado_consulta').html(respuesta);
					$('#calificacion').select();
				});
			});

            $('#curso_id').on('change',function()
			{
                
				$('#resultado_consulta').html( "" );

                $('#asignatura_id').html('<option value=""></option>');

                if ( $(this).val() == '') { return false; }

                $('#div_cargando').show();

                var periodo_lectivo_id = $('#periodo_lectivo_id').val();
                //var periodo_lectivo_id = "null";
                
                var url = "{{ url('calificaciones_opciones_select_asignaturas_del_curso') }}" + "/" + $('#curso_id').val() + "/null" + "/" + periodo_lectivo_id + "/Activo";

                //console.log( url );

                $.ajax({
                    url: url,
                    type: 'get',
                    success: function(datos){

                        $('#div_cargando').hide();
                        
                        $('#asignatura_id').html( datos );
                        $('#asignatura_id').focus();
                    }
                });					
			});

            $('#asignatura_id').on('change',function()
            {
                $('#resultado_consulta').html( "" );
            });

			/*
					Guardar
			*/
			$(document).on('click','#btn_guardar',function(event){
				event.preventDefault();

				var calificacion = parseFloat( $('#calificacion').val() );

				if ( $('#calificacion').val() == '' )
				{
					$('#calificacion').select();
					alert('Debe ingresar una calificación.');
					return false;
				}

				var escala_valoracion_maxima = parseFloat($('#escala_valoracion_maxima').val());
				console.log( [ calificacion, escala_valoracion_maxima ] );

				if ( calificacion > escala_valoracion_maxima )
				{
					$('#calificacion').select();
					alert('La calificación es mayor que la máxima Escala de valoración permitida: ' +  $('#escala_valoracion_maxima').val() );
					return false;
				}

				$('#div_spin').show();
				$('#div_mensaje').hide();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_actualizar');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();

				$('#resultado_consulta').fadeOut(500);
				$('#resultado_consulta').html("");
					
				// Enviar formulario vía POST
				$.post(url,datos,function(respuesta2){
					$('#div_spin').hide();
					$('#resultado_consulta').html( respuesta2 );
					$('#resultado_consulta').fadeIn(500);					
					$('#div_mensaje').show();
				});
			});

		});
	</script>
@endsection