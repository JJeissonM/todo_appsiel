@extends('layouts.reportes')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('sidebar')
	{{ Form::open(['url'=> $reporte->url_form_action . '?id=' . Input::get('id') ,'id'=>'form_consulta', 'data-url_form_action' => $reporte->url_form_action]) }}

		@foreach( $lista_campos as $campo)
			<?php 
				$requerido = '';
				if ( $campo['requerido'] )
				{
					$requerido = '*';
				}
			?>
			<div class="form-group">
				{{ Form::label( $campo['name'], $requerido.$campo['descripcion'] ) }}
				@if( is_array($campo['opciones']) )
					{{ Form::{$campo['tipo']}( $campo['name'], $campo['opciones'], null, $campo['atributos'] ) }}
				@else
					{{ Form::{$campo['tipo']}( $campo['name'], null, $campo['atributos'] ) }}
				@endif
			</div>
		@endforeach

		{{ Form::label( 'tam_hoja', 'Tamaño hoja' ) }}
		{{ Form::select('tam_hoja',['letter'=>'Carta','folio'=>'Oficio','pos_80mm'=>'POS 80mm'],null,['id'=>'tam_hoja']) }}

		<br>
		{{ Form::label( 'orientacion', 'Orientación' ) }}
		{{ Form::select('orientacion',['Portrait'=>'Vertical','Landscape'=>'Horizontal'],null,['id'=>'orientacion']) }}

		{{ Form::hidden( 'reporte_instancia', $reporte ) }}
		{{ Form::hidden( 'url_id',Input::get('id') ) }}

		<br>

		<!-- <button type="submit"> ir </button> -->

		<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar </a>

	{{ Form::close() }}
@endsection


@section('contenido')
	<div class="col-md-12 marco_formulario">		
		
		<div style="display: flex; justify-content: justify-content-center">
			<span>
				{{ Form::bsBtnPdf( $reporte->descripcion ) }}
				{{ Form::bsBtnExcel( 'Exportar a Excel' ) }}


				<div id="div_btns_barcodes" >

				</div>

				<input type="hidden" name="ip_printer" id="ip_printer" value="{{ config('inventarios.ip_printer') }}">
				<input type="hidden" name="connector_type" id="connector_type" value="{{ config('inventarios.connector_type') }}">
				
				<!--
				<a onclick="makeRequest('{ {url('/sys_test_print_example_rawbt')}}')" class="btn-gmail" id="btn_print_barcodes" style="display: inline-block;" href="#" title="Etiquetas de códigos de barra"><i class="fa fa-print"></i></a>
				<a class="btn-gmail" id="btn_feed_paper" style="display: inline-block;" href="#" title="Alimentar papel"><i class="fa fa-arrow-up"></i></a>
				
				<a class="btn-gmail" id="btn_feed_reverse_paper" style="display: inline-block;" href="#" title="Retroceder papel"><i class="fa fa-arrow-down"></i></a>	
				--> 			

			</span>
		</div> 
		{{ Form::Spin( 42 ) }}
		{{ Form::hidden( 'reporte_id', $reporte->id, ['id'=>'reporte_id'] ) }}
		<div class="table-responsive" id="table_content" >
			<div id="resultado_consulta" style="position: relative">

			</div>
		</div>	
			
		<br/>
		
	</div>
@endsection

@section('scripts')

	<script src="{{ asset( 'assets/js/inventarios/barcodes_printing.js?aux=' . uniqid() )}}"></script>

	<script type="text/javascript">
		$(document).ready(function(){

			var URL = "{{ url('/') }}";

			var url_pdf_ori = $('#btn_pdf').attr('href');

			$("#form_consulta > [required='required']").each(function(){
				$(this).prev('label').append('*');
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				event.preventDefault();
				$('#resultado_consulta').html( "" );
				$('#btn_excel').hide();
				$('#btn_pdf').hide();

				$('#btn_pdf').attr('href', url_pdf_ori);

				if( !validar_requeridos() )
				{
					return false;
				}

				$('#div_cargando').show();
				$('#div_spin').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#div_spin').hide();
					$('#resultado_consulta').html(respuesta);

					$('.columna_oculta').show();

					if (form_consulta.attr('data-url_form_action') != 'inv_etiquetas_codigos_barra' ) {
						$('#btn_excel').show(500);
						$('#btn_pdf').show(500);

						var url_pdf = $('#btn_pdf').attr('href');
						var n = url_pdf.search('a3p0');
						if ( n > 0) {
							var new_url = url_pdf.replace( 'a3p0', 'generar_pdf/' + $("#reporte_id").val() + '?tam_hoja=' + $("#tam_hoja").val() + '&orientacion=' + $("#orientacion").val() );
						}
						
						$('#btn_pdf').attr('href', new_url);

						return false;
					}

					create_btns_for_print_barcodes();
					
				});
			});

			// Para algunos reportes de calificaciones
			$('#periodo_lectivo_id').on('change',function()
			{
				$('#periodo_id').html('<option value=""></option>');
				if ( $(this).val() == '') { return false; }

				var periodo_lectivo_id = $('#periodo_lectivo_id').val();

	    		$('#div_cargando').show();

				var url = "{{ url('get_select_periodos') }}" + "/" + periodo_lectivo_id;

				$.ajax({
		        	url: url,
		        	type: 'get',
		        	success: function(datos){

	    				$('#periodo_id').html( datos );
						$('#periodo_id').focus();

						// Otro llamado para llenar el select de cursos
						var url = "{{ url('get_select_cursos') }}" + "/" + periodo_lectivo_id;

						$.ajax({
							url: url,
							type: 'get',
							success: function(datos){

								$('#div_cargando').hide();
								
								$('#curso_id').html( datos );
								$('#curso_id').focus();
							}
						});
			        }
			    });
			});

			// Para algunos reportes de calificaciones
			$('#curso_id').on('change',function()
			{

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
				}else{

					// Debe haber Select Asignatura
					$('#asignatura_id').html('<option value=""></option>');

					if ( $(this).val() == '') { return false; }

		    		$('#div_cargando').show();

					var url = "{{ url('calificaciones_opciones_select_asignaturas_del_curso') }}" + "/" + $('#curso_id').val() + "/null" + "/" + $('#periodo_lectivo_id').val() + "/Activo";

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
				}

					
			});

			$('#teso_medio_recaudo_id').on('change',function()
			{
				$('#resultado_consulta').html('');
				
				$('#teso_caja_id').html('<option value=""></option>');
				$('#teso_caja_id').removeAttr( 'required' );

				$('#teso_cuenta_bancaria_id').html('<option value=""></option>');
				$('#teso_cuenta_bancaria_id').removeAttr( 'required' );

				if ( $(this).val() == '') { return false; }

	    		$('#div_cargando').show();

				// NOTA: El valor teso_medio_recaudo_id está Constante !!!!!!
	    		if ( $(this).val() == 1 ) 
	    		{
	    			//Efectivo
	    			var url = "{{ url('tesoreria/get_cajas_to_select') }}";
	    			$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){

			        		$('#div_cargando').hide();
		    				
		    				$('#teso_caja_id').html( datos );
		    				$('#teso_caja_id').attr( 'required', 'required' );
		    				$('#teso_cuenta_bancaria_id').removeAttr( 'required' );
							$('#teso_caja_id').focus();
				        }
				    });
	    		}else{
	    			var url = "{{ url('tesoreria/get_ctas_bancarias_to_select') }}";
	    			$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){

			        		$('#div_cargando').hide();
		    				
		    				$('#teso_cuenta_bancaria_id').html( datos );
		    				$('#teso_cuenta_bancaria_id').attr( 'required', 'required' );
		    				$('#teso_caja_id').removeAttr( 'required' );
							$('#teso_cuenta_bancaria_id').focus();
				        }
				    });
	    		}
				
			});

			$('#nom_doc_encabezado_id').on('change',function()
			{
				
				if ( $(this).val() == '')
				{
					$('#fecha_desde').removeAttr( 'disabled' );
					$('#fecha_hasta').removeAttr( 'disabled' );
					$('#fecha_desde').attr( 'required', 'required' );
					$('#fecha_hasta').attr( 'required', 'required' );
					return false;
				}

				$('#fecha_desde').removeAttr( 'required' );
				$('#fecha_hasta').removeAttr( 'required' );
				$('#fecha_desde').attr( 'disabled', 'disabled' );
				$('#fecha_hasta').attr( 'disabled', 'disabled' );
				
			});

			/**
			 * 
			*/
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

				generar_pdf_boletines();
			});

			function generar_pdf_boletines()
			{
				arr_ids_estudiantes = JSON.parse($("#ids_estudiantes").val());
				arr_ids_estudiantes.shift();
				
				$.get("../../calif_delete_pdfs_of_folder_of_curso_id" + "/" + $('#curso_id').val(), function(respuesta){ 
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
					
					window.open( '../../calif_merge_pdfs_and_download_by_curso/' + $('#curso_id').val() + '/'  + $('#tam_hoja').val(), '_blank');

					return true;
				}

				// pop top value 
				var estudiante_id = arr_ids_estudiantes[0];
				arr_ids_estudiantes.shift(); 
				//var url = '../../calif_generar_pdf_un_boletin';
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
					ejecucion_recursiva_generar_un_boletin();
				});
			}

		});

		
	</script>
@endsection