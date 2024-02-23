@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	<div class="row">
		<div class="col-sm-4">
			<div class="panel panel-primary">
				<div class="panel-heading" align="center">
					<h5>Generar listados de estudiantes</h5>
				</div>
				
				<div class="panel-body">
					{{ Form::open(['url'=>'matriculas_estudiantes_generar_listado','id'=>'form_consulta']) }}
					
						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('tipo_listado', null, 'Tipo de listado', ['1'=>'Listado de estudiantes', '2'=>'Ficha de datos básicos', '3'=>'Listado de datos básicos', '4'=>'Listado de usuarios', '5'=>'Datos de matrículas y pensiones'], []) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('periodo_lectivo_id', null, 'Año lectivo', $periodos_lectivos, ['required' => 'required']) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('sga_grado_id', null, 'Grado', $grados, []) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('curso_id', null, 'Curso', ['Todos'=>'Todos'], []) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('tam_hoja', null, 'Tamaño hoja', ['Letter'=>'Carta','Legal'=>'Oficio'], []) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('orientacion', null, 'Orientación hoja', ['portrait'=>'Vertical','landscape'=>'Horizontal'], []) }}
                        </div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('tam_letra',3.5,'Tamaño Letra',['2.5'=>'09','3'=>'10','3.5'=>'11','4'=>'12','4.5'=>'13','5'=>'14','5.5'=>'15'],[]) }}
						</div>
					{{Form::close()}}

					<div class="col-sm-offset-3 col-sm-6">
						<button class="btn btn-primary" id="btn_generar"> <i class="fa fa-list"></i> Generar listado </button>
						{{ Form::Spin(48) }}
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-8">
			
			<div style="display: flex; justify-content: justify-content-center">
				<span>				
					{{ Form::bsBtnPdf( 'Listado de estudiantes' ) }}
					{{ Form::bsBtnExcel( 'Exportar a Excel' ) }}
				</span>
			</div> 
			{{ Form::Spin( 42 ) }}
			<div class="col-md-12 marco_formulario" id="resultado_consulta" style="width: 100%; overflow: auto; white-space: nowrap;">
				
			</div>	
		</div>
	</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			var URL = "{{ url('/') }}";
			var url_pdf_ori = $('#btn_pdf').attr('href');

			$('#sga_grado_id').change( function(){
				var grado_id = $('#sga_grado_id').val();
				$('#div_cargando').fadeIn();
				$('#curso_id').html( '<option>Espere...</option>' );

				var url = '../../get_cursos_del_grado/' + grado_id;

				$.get( url, function( datos ) {
			        $('#div_cargando').hide();
			        $('#curso_id').html( datos );
				});
			});

			$('#tipo_listado').change( function(){
				if ($(this).val() == '3' ) {
					$('#orientacion').val('landscape');
				}else{
					$('#orientacion').val('portrait');
				}
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
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace( 'a3p0', 'generar_pdf/999' + '?tam_hoja=' + $("#tam_hoja").val() + '&orientacion=' + $("#orientacion").val() );
					}
					
					$('#btn_pdf').attr('href', new_url);
				});
			});

			//var url_pdf_ori = $('#btn_pdf').attr('href');
		});
	</script>
@endsection