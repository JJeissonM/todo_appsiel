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
@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-md-8 col-md-offset-2 marco_formulario">
		    <h4>Parámetros de selección</h4>
		    <hr>

		    {{ Form::open( [ 'url' => 'calificaciones/boletines/generarPDF', 'files' => true, 'id' => 'formulario'] ) }}

		    	<div class="row">
		    		<div class="col-md-6">
		    			<div class="row" style="padding:5px;">
							{{ Form::bsSelect('periodo_lectivo_id','','Año lectivo',$periodos_lectivos,['required' => 'required']) }}
						</div>

		    			<div class="row" style="padding:5px;">
							{{ Form::bsSelect('periodo_id','','Periodo',[],['required' => 'required']) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('curso_id','','Curso',$cursos,['required' => 'required']) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('formato','','Formato', $formatos ,['required' => 'required']) }}

							<!-- , 'pdf_boletines_5' => 'Formato # 5 (prescolar)' -->
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('mostrar_areas',1,'Mostrar áreas',['No'=>'No','Si'=>'Si'],[]) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('mostrar_nombre_docentes',1,'Mostrar nombre de docentes',['No'=>'No','Si'=>'Si'],[]) }}
						</div>

						<?php 
							echo campo_firma('Firma para Rector(a)', 'firma_rector');
						?>
		    		</div>
		    		<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('mostrar_escala_valoracion',1,'Mostrar Escala de valoración',['No'=>'No','Si'=>'Si'],[]) }}
						</div>

		    			<div class="row" style="padding:5px;">
							{{ Form::bsSelect('tam_hoja','','Tamaño hoja',['letter'=>'Carta','folio'=>'Oficio'],[]) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('tam_letra',4,'Tamaño Letra',['2.5'=>'10','3'=>'11','3.5'=>'12','4'=>'13','4.5'=>'14','5'=>'15','5.5'=>'16'],[]) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('convetir_logros_mayusculas',1,'Convertir logros a mayúsculas',['No'=>'No','Si'=>'Si'],[]) }}
						</div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('mostrar_usuarios_estudiantes',1,'Mostrar usuario de estudiantes',['No'=>'No','Si'=>'Si'],[]) }}
						</div>

						<?php 
							echo campo_firma('Firma para Director(a) de grupo', 'firma_profesor');
						?>
		    		</div>
		    	</div>

				<div style="padding:5px;" align="center">
					<a class="btn btn-primary btn-sm" id="btn_imprimir" target="_blank">
						<i class="fa fa-btn fa-print"></i> Imprimir
					</a>
				</div>

			{{Form::close()}}
		
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#periodo_lectivo_id').focus();

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

			$("#periodo_id").on('change',function(){
				if ( $(this).val() == '') { return false; }
				$('#curso_id').focus();
			});

			$("#curso_id").on('change',function(){
				if ( $(this).val() == '') { return false; }
				$('#btn_imprimir').focus();
			});	

			$("#btn_imprimir").on('click',function(){
				$('#formulario').submit();
			});		
		});
	</script>
@endsection