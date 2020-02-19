@extends('layouts.reportes')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('sidebar')
	{{ Form::open(['url'=> $reporte->url_form_action,'id'=>'form_consulta']) }}

		@foreach( $lista_campos as $campo)
			{{ Form::label( $campo['name'], $campo['descripcion'] ) }}
			@if( is_array($campo['opciones']) )
				{{ Form::{$campo['tipo']}( $campo['name'], $campo['opciones'], null, $campo['atributos'] ) }}
			@else
				{{ Form::{$campo['tipo']}( $campo['name'], null, $campo['atributos'] ) }}
			@endif
			<br>
		@endforeach

		{{ Form::label( 'tam_hoja', 'Tamaño hoja' ) }}
		{{ Form::select('tam_hoja',['letter'=>'Carta','folio'=>'Oficio'],null,['id'=>'tam_hoja']) }}

		{{ Form::label( 'orientacion', 'Orientación' ) }}
		{{ Form::select('orientacion',['Portrait'=>'Vertical','Landscape'=>'Horizontal'],null,['id'=>'orientacion']) }}

		{{ Form::hidden( 'reporte_instancia', $reporte ) }}
		{{ Form::hidden( 'url_id',Input::get('id') ) }}
		{{ Form::hidden( 'url_id_modelo',Input::get('id_modelo') ) }}

		<br>

		<!-- <button type="submit"> ir </button> -->

		<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar </a>

	{{ Form::close() }}
@endsection


@section('contenido')
		<div class="col-md-12 marco_formulario">
			<br/>
			{{ Form::bsBtnExcel( $reporte->descripcion ) }}
			{{ Form::bsBtnPdf( $reporte->descripcion ) }}
			{{ Form::Spin( 42 ) }}
			{{ Form::hidden( 'reporte_id', $reporte->id, ['id'=>'reporte_id'] ) }}
			
			<div id="resultado_consulta">

			</div>	
		</div>
@endsection

@section('scripts')
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

				if(!valida_campos()){
					alert('Los campos marcados con asterisco son requeridos.');
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
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace( 'a3p0', 'generar_pdf/' + $("#reporte_id").val() + '?tam_hoja=' + $("#tam_hoja").val() + '&orientacion=' + $("#orientacion").val() );
					}
					
					$('#btn_pdf').attr('href', new_url);

				});
			});

			function valida_campos(){
				var valida = true;
				$("#form_consulta > [required='required']").each(function(){
					if( $(this).val() == ''  )
					{
						valida = false;
						return;
					}	
				});
				
				return valida;
			}

			// Para algunos reportes de calificaiones
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

		        		$('#div_cargando').hide();
	    				
	    				$('#periodo_id').html( datos );
						$('#periodo_id').focus();
			        }
			    });
			});

		});

		
	</script>
@endsection