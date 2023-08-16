@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>


<style>
    table {
        border-collapse: separate;
        border-spacing: 1px;
    }
    .tr_abuelo {
      background-color: #90a4ae;
      font-weight: bold;
    }
    .tr_padre {
      background-color: #b0bec5;
      font-weight: bold;
    }
    .tr_padre > td:first-child {
      padding-left: 2rem;
    }
    .tr_hijo {
      background-color: #cfd8dc;
    }
    .tr_hijo > td:first-child {
      padding-left: 4rem;
    }
    .tr_cuenta {
      background-color: #eceff1;
    }
    .tr_cuenta > td:first-child {
      padding-left: 6rem;
    }

    .simbolo_moneda{
        float: left;
    }
  </style>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-12">

			{{ Form::open(['url'=>'contab_ajax_generacion_eeff','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-3">
						{{ Form::label('reporte_id','Reporte') }}
						{{ Form::select('reporte_id',[ 'balance_general' => 'Balance general', 'estado_resultados' => 'Estado de resultados'],null,[ 'class' => 'form-control','id'=>'reporte_id']) }}
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-2">
								&nbsp;
							</div>
							<div class="col-sm-2">
								{{ Form::label('etiqueta1','ETIQUETA',[]) }}
							</div>
							<div class="col-sm-4">
								{{ Form::label('etiqueta1','DESDE',[]) }}
							</div>
							<div class="col-sm-4">
								{{ Form::label('etiqueta1','HASTA',[]) }}
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2">
								{{ Form::label('etiqueta_1','Lapso 1:',[]) }}
							</div>
							<div class="col-sm-2">
								{{ Form::text('lapso1_lbl',date('Y'),['class'=>'form-control','id'=>'lapso1_lbl']) }}
							</div>
							<div class="col-sm-4">
								{{ Form::date('lapso1_ini',date('Y-01-01'),['class'=>'form-control','id'=>'lapso1_ini']) }}
							</div>
							<div class="col-sm-4">
								{{ Form::date('lapso1_fin',date('Y-12-31'),['class'=>'form-control','id'=>'lapso1_fin']) }}
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-sm-6">
								{{ Form::bsSelect('modalidad_reporte',null,'Modalidad del Reporte',[ 'acumular_movimiento' => 'Acumular movimiento', 'acumular_periodo' => 'Acumular solo periodo'],[ 'class' => 'form-control','id'=>'modalidad_reporte']) }}
							</div>
							<div class="col-sm-6">
								{{ Form::bsSelect('detallar_cuentas',null,'Detallar Cuentas',[ 1 => 'Si', 0 => 'No' ],[ 'class' => 'form-control','id'=>'detallar_cuentas']) }}
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<br>
						<!-- --><a href="#" class="btn btn-danger bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> OLD Generar</a> 
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar2" data-balance_general="[ 1, 2, 3]" data-estado_resultados="[ 4, 5, 6]"><i class="fa fa-play"></i> Generar</a>
						<input type="hidden" id="ids_clases_cuentas">
						<input type="hidden" id="ids_grupos_padres">
						<input type="hidden" id="ids_grupos_hijos">
						<input type="hidden" id="ids_cuentas">
					</div>
				</div>
			{{ Form::close() }}
				<!--	<button id="btn_ir">ir</button>		-->
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('generacion_eeff') }}
			{{ Form::bsBtnPdf('estados_financieros') }}

			<table width="100%" id="tabla_resultados" style="display: none;">
				<thead>
					<tr>
						<th> &nbsp; </th>
						<th> <span id="lbl_anio"></span> </th>
						<th> &nbsp; </th>
					</tr>
				</thead>
				<tbody>
					
				</tbody>
				<tfoot style="display: none;">					
				</tfoot>				
			</table>

			{{ Form::Spin( 42 ) }}
			
			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')

	<script src="{{ asset( 'assets/js/contabilidad/eeff.js?aux=' . uniqid() )}}"></script>

	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#reporte_id').focus();

			$('#reporte_id').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_inicial').focus();				
				}		
			});

			$('#fecha_inicial').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_final').focus();				
				}		
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos()){
					alert('Debe diligenciar todos los campos.');
					return false;
				}

				$('#resultado_consulta').html( '' );
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
					
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace('a3p0','contab_pdf_eeff?'+datos);
					}else{
						n = url_pdf.search('contab_pdf_eeff');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'contab_pdf_eeff?' + datos;
					}					
					
					$('#btn_pdf').attr('href', new_url);
					$('#btn_pdf').attr('target', '_blank');
				});
			});

			function valida_campos(){
				var valida = true;
				if($('#lapso1_ini').val()=='' || $('#lapso1_fin').val()=='' || $('#reporte_id').val()==''|| $('#lapso1_lbl').val()=='')
				{
					valida = false;
				}

				if($('#lapso2_lbl').val() != ''){
					if($('#lapso2_ini').val()=='' || $('#lapso2_fin').val()=='' || $('#lapso2_lbl').val()=='' || $('#reporte_id').val()==''){
						valida = false;
					}
				}

				return valida;
			}
		});

		
	</script>
@endsection