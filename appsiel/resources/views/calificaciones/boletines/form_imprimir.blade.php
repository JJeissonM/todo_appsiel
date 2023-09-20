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

				<div style="padding:5px;" align="center">
					<a class="btn btn-primary btn-sm" id="btn_imprimir" target="_blank">
						<i class="fa fa-print"></i> Generar PDF
					</a>
					<a class="btn btn-primary btn-sm" id="btn_generar_pdfs" target="_blank">
						<i class="fa fa-print"></i> Generar PDF
					</a>					
				</div>

				<div style="padding:5px; display: none; text-align: center; color: red;" id="message_print">
					El informe se generó en una nueva ventana del navegador.
					<br>
					Deben estar activas las ventanas emergentes.
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
	
		var arr_ids_estudiantes, cache_key, pdf_name;
		
		$(document).ready(function(){
			
			$('#periodo_lectivo_id').focus();
			$('#btn_imprimir').hide();

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
				$('#curso_id').focus();
			});

			$("#curso_id").on('change',function(){
				if ( $(this).val() == '') { return false; }
				$('#message_print').hide();
				
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

				if ( $(this).val() == '') { 
					$('#btn_imprimir').hide();
					$('#btn_generar_pdfs').show();
				}else{
					$('#btn_imprimir').show();
					$('#btn_generar_pdfs').hide();
				}
				
			});

			$("#btn_imprimir").on('click',function(){
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

				var arr_ids = '0';
				$("#estudiante_id option").each(function(i){
					if ($.isNumeric( $(this).val() )) {
						arr_ids += ',' + $(this).val() ;
					}					
				});

				$("#ids_estudiantes").val( '[' + arr_ids + ']' );
				
				cache_key = 'pdf_boletines_curso_id_' + $('#curso_id').val();
				
				pdf_name = 'boletines_curso_' + $("#curso_id option:selected").text();

				generar_pdf_boletines();
			});

			function generar_pdf_boletines()
			{
				arr_ids_estudiantes = JSON.parse($("#ids_estudiantes").val());
				arr_ids_estudiantes.shift();
				
				$.get("../../core_forget_cache" + "/" + cache_key, function(respuesta){ 
					// fires off the first call 
					ejecucion_recursiva_generar_un_boletin();					
				});
			}
    
			// The recursive function 
			function ejecucion_recursiva_generar_un_boletin() { 
				
				// terminate if array exhausted 
				if (arr_ids_estudiantes.length === 0) 
				{
					$('#div_cargando').hide();
					$('#btn_generar_pdfs').children('.fa-spinner').attr('class','fa fa-print');
					$('#message_print').show();

					window.open( '../../core_descargar_pdf_desde_cache/' + cache_key + '/' + pdf_name, '_blank');

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
					console.log( res, 'Ok.');
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