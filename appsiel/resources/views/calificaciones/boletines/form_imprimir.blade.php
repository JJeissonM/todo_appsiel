@extends('layouts.principal')

<?php 
	function campo_firma($etiqueta, $name){
		return '<div class="input-group">
						  <div class="input-group-prepend">
						    <a href="#" data-toggle="tooltip" data-placement="right" title="Solo imágenes PNG. Dimensiones: 250px X 70px"> <i class="fa fa-question-circle"></i> </a> <span class="input-group-text">'.$etiqueta.'</span>
						  </div>
						  <div class="custom-file">
						    <input type="file" class="custom-file-input" name="'.$name.'" accept="image/x-png">
						  </div>
						</div>';
	}
?>

@section('estilos_1')
	<style type="text/css">
			
		/* Style the buttons that are used to open and close the accordion panel */
		.accordion {
		  background-color: #eee;
		  color: #444;
		  cursor: pointer;
		  padding: 18px;
		  width: 100%;
		  text-align: left;
		  border: none;
		  outline: none;
		  transition: 0.4s;
		  font-weight: bold;
		}

		/* Add a background color to the button if it is clicked on (add the .active class with JS), and when you move the mouse over it (hover) */
		.active, .accordion:hover {
		  background-color: #ccc;
		}

		/* Style the accordion panel. Note: hidden by default */
		.panel {
		  padding: 0 18px;
		  background-color: white;
		  display: none;
		  overflow: hidden;
		}
		.campo:hover { background-color: #9fda91; }
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-md-8 col-md-offset-2 marco_formulario">
		    <h4>Parámetros de selección</h4>
		    <hr>

		    {{ Form::open( [ 'url' => 'calificaciones/boletines/generarPDF', 'files' => true, 'id' => 'formulario'] ) }}

		    	<div class="container-fluid">

					<button class="accordion"> Selección datos básicos <a href="#" class="close" data-dismiss="alert" aria-label="close">&plus;</a></button>
					<div class="panel show">
						@include('calificaciones.boletines.formulario_imprimir.datos_basicos')
					</div>

					<button class="accordion"> Opciones Adicionales <a href="#" class="close" data-dismiss="alert" aria-label="close">&plus;</a></button>
					<div class="panel">
						@include('calificaciones.boletines.formulario_imprimir.opciones_adicionales')
					</div>

					<button class="accordion"> Opciones de formato <a href="#" class="close" data-dismiss="alert" aria-label="close">&plus;</a></button>
					<div class="panel">
						@include('calificaciones.boletines.formulario_imprimir.opciones_formato')
					</div>

		    	</div>
				
				@if( config('calificaciones.modo_impresion_boletines') == 'ajax')

					<div class="well">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('forma_generar_pdfs', $parametros['forma_generar_pdfs'], 'Forma de generar los PDFs',['Un solo PDF con todos los estudiantes.','Un PDF individual por cada estudiante (Archivo comprimido)'],[]) }}
						</div>
					</div>
				@endif

				<div style="padding:5px;" align="center">
					@if( config('calificaciones.modo_impresion_boletines') == 'ajax')
						<a class="btn btn-primary btn-sm" id="btn_imprimir2" target="_blank">
							<i class="fa fa-print"></i> Descargar PDF
						</a>
						<a class="btn btn-primary btn-sm" id="btn_generar_pdfs" target="_blank">
							<i class="fa fa-print"></i> Generar PDF
						</a>
					@else
						<a class="btn btn-primary btn-sm" id="btn_imprimir" target="_blank">
							<i class="fa fa-print"></i> Descargar PDF
						</a>
					@endif				
				</div>

				<div style="padding:5px; display: none; text-align: center; color: red;" id="message_counting">
					Por favor espere.
					<br>
					Generando PDFs... <span id="counter" style="color:#9c27b0"></span> restantes
				</div>

				<div style="padding:5px; display: none; text-align: center; color: red;" id="message_print">
					El informe se generó en una nueva ventana del navegador.
					<br>
					Deben estar activas las ventanas emergentes.
					<br>
					<button class="btn btn-sm btn-info" id="download_zip_again">Si el archivo no se descargó, haga clic aquí para descargar nuevamente</button>
				</div>

				{{ Form::hidden('url_id',Input::get('id')) }}

				<input type="hidden" id="ids_estudiantes">

			{{Form::close()}}
		
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
	
		var arr_ids_estudiantes;
		var restantes;
		
		$(document).ready(function(){
			
			$('#periodo_lectivo_id').focus();
			$('#btn_imprimir2').hide();

			$('.accordion').on('click',function(e)
			{
				e.preventDefault();
			});

			// Para algunos reportes de calificaiones
			$('#periodo_lectivo_id').on('change',function()
			{
				$('#periodo_id').html('<option value=""></option>');
				if ( $(this).val() == '') { return false; }
				$('#message_print').hide();
				$('#message_counting').hide();

				var periodo_lectivo_id = $('#periodo_lectivo_id').val();

	    		$('#div_cargando').show();

				var url = "{{ url('get_select_periodos') }}" + "/" + periodo_lectivo_id;

				$.ajax({
		        	url: url,
		        	type: 'get',
		        	success: function(datos){

		        		$('#div_cargando').hide();
	    				
	    				$('#periodo_id').html( datos );
						$('#periodo_id').focus();
			        }
			    });
			});

			$("#periodo_id").on('change',function(){
				if ( $(this).val() == '') { return false; }
				$('#message_print').hide();
				$('#message_counting').hide();
				$('#curso_id').focus();
			});

			$("#curso_id").on('change',function(){
				if ( $(this).val() == '') { return false; }
				$('#message_print').hide();
				$('#message_counting').hide();
				
				if( $('#estudiante_id').html() !== undefined )
				{
					$('#estudiante_id').html('<option value=""></option>');

					if ( $(this).val() == '') { return false; }

		    		$('#div_cargando').show();

					var url = "{{ url('get_todos_estudiantes_matriculados') }}" + "/" + $('#periodo_lectivo_id').val() + "/" + $('#curso_id').val();

					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){

			        		$('#div_cargando').hide();
		    				
		    				$('#estudiante_id').html( datos );
							$('#estudiante_id').focus();
				        }
				    });
				}

				$('#btn_imprimir').focus();
			});

			
			$("#estudiante_id").on('change',function(){				
				$('#message_print').hide();
				$('#message_counting').hide();
			});

			$("#btn_imprimir").on('click',function(){
				if ( !validar_requeridos() )
				{
					return false;
				}
				
				$('#formulario').submit();
			});

			$("#btn_imprimir2").on('click',function(){
				if ( !validar_requeridos() )
				{
					return false;
				}
				
				$('#formulario').submit();
			});

			$('#btn_generar_pdfs').click(function(event){
        
				event.preventDefault();
				
				if(!validar_requeridos()){
					alert('Faltan campor por diligenciar.');
					return false;
				}

				$(this).children('.fa-print').attr('class','fa fa-spinner fa-spin');
				$('#div_cargando').show();
				$('#message_print').hide();
				$('#message_counting').show();

				var arr_ids = '0';
				if ( $("#estudiante_id").val() == '' ) { 
					$("#estudiante_id option").each(function(i){
						if ($.isNumeric( $(this).val() )) {
							arr_ids += ',' + $(this).val() ;
						}
					});
				}else{
					arr_ids += ',' + $("#estudiante_id").val() ;
				}				

				$("#ids_estudiantes").val( '[' + arr_ids + ']' );

				generar_pdf_boletines();
			});

			/*
			 * download_zip_again
			*/
			$('#download_zip_again').click(function(event){        
				event.preventDefault();
				window.open( '../../calif_download_zip_of_curso_id/' + $('#curso_id').val(), '_blank');
			});

			function generar_pdf_boletines()
			{
				arr_ids_estudiantes = JSON.parse($("#ids_estudiantes").val());
				arr_ids_estudiantes.shift(); // Retirar ID cero (0) del select			

				restantes = arr_ids_estudiantes.length;

				$('#counter').html( restantes );
				
				$.get("../../calif_delete_pdfs_of_folder_of_curso_id" + "/" + $('#curso_id').val(), function(respuesta){ 
					// fires off the first call 
					ejecucion_recursiva_generar_un_boletin();					
				});
			}
    
			// The recursive function 
			function ejecucion_recursiva_generar_un_boletin() { 
				
				// Si ya se generaron todos los PDFs
				if (arr_ids_estudiantes.length === 0) 
				{
					$('#div_cargando').hide();
					$('#btn_generar_pdfs').children('.fa-spinner').attr('class','fa fa-print');
					$('#message_print').show();
					$('#message_counting').hide();
					
					if ( $('forma_generar_pdfs').val() == 0) {
						// Un Solo PDF
						window.open( '../../calif_merge_pdfs_and_download_by_curso/' + $('#curso_id').val(), '_blank');
					}else{
						// PDFs individuales
						window.open( '../../calif_create_zip_of_folder_of_curso_id/' + $('#curso_id').val(), '_blank');
					}

					return true;
				}

				// pop top value 
				var estudiante_id = arr_ids_estudiantes[0];
				arr_ids_estudiantes.shift();
				var url = '../../calif_generar_pdf_un_boletin';

				var formData = new FormData(document.getElementById('formulario'));
				formData.append('estudiante_id', estudiante_id);

				$.ajax({
					url: url,
					type: "post",
					dataType: "html",
					data: formData,
					cache: false,
					contentType: false,
					processData: false
				})
				.done(function(res){
					restantes--;
					document.getElementById('counter').innerHTML = restantes;
					ejecucion_recursiva_generar_un_boletin();
				});
			}

			// Accordion
			var acc = document.getElementsByClassName("accordion");
			var i;

			for (i = 0; i < acc.length; i++) {
			  acc[i].addEventListener("click", function() {
			      this.classList.toggle("active");
			      var panel = this.nextElementSibling;
			      if (panel.style.display === "block") {
			          panel.style.display = "none";
			      } else {
			          panel.style.display = "block";
			      }
			  });
			}

		});
	</script>
@endsection