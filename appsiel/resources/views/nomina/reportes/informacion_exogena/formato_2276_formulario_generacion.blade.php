@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
	$anio = (int)date('Y');
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}


	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">				
				{{ Form::open(['url'=>'nom_ajax_formato_2276_informacion_exogena','id'=>'form_consulta']) }}
					<div class="row">
						<div class="col-sm-6">
							{{ Form::label('periodo','Período') }}
							<br/>
							{{ Form::bsFecha('fecha_inicio_periodo', ($anio-1).'-01-01', 'Desde', '', []) }}
							{{ Form::bsFecha('fecha_fin_periodo', ($anio-1).'-12-31', 'Hasta', '', []) }}
						</div>
						<div class="col-sm-3">
							{{ Form::label('parametros_seleccion_id','Parametros selección') }}
							<br/>
							{{ Form::select('parametros_seleccion_id',$parametros,null,[ 'class' => 'form-control', 'id' => 'parametros_seleccion_id', 'required' => 'required' ]) }}
						</div>
						<div class="col-sm-3">
							{{ Form::label(' ','.') }}
							<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
						</div>
					</div>				
				{{ Form::close() }}				
			</div>
		</div>
	</div>

	<hr>

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('certificado_ingresos_y_retenciones') }}
			{{ Form::bsBtnPdf('certificado_ingresos_y_retenciones') }}

			{{ Form::Spin(48) }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				$('#resultado_consulta').html( '' );
				$('#div_spin').show();
				$('#div_cargando').show();


				$('#btn_excel').hide();
				$('#btn_pdf').hide();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#div_spin').hide();
					$('#resultado_consulta').html(respuesta);
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace('a3p0','nomina_pdf_certificado_ingresos_y_retenciones?'+datos);
					}else{
						n = url_pdf.search('nomina_pdf_certificado_ingresos_y_retenciones');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'nomina_pdf_certificado_ingresos_y_retenciones?' + datos;
					}
					
					$('#btn_pdf').attr('href', new_url);
				});
			});


			
			$(document).on('hover','td',function(){
				var fila_encabezado  = $('table thead tr').eq( 1 ); // La segunda fila del encabezado
				//console.log( fila_encabezado.find('th').eq(2).html() );
				//var celda_encabezado = $('table thead tr[1] th').eq( $(this).index() );
				var celda_encabezado = fila_encabezado.find('th').eq( $(this).index() );
				//var etiqueta_mostrar = $(this).parent('tr').attr('title') + ": " + celda_encabezado.attr('title');
				$(this).attr( 'title', celda_encabezado.attr('title') );
			});

		});

		
	</script>
@endsection