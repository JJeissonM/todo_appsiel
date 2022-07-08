@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-12">
			
			{{ Form::open(['url'=>'contab_ajax_auxiliar_por_cuenta','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						{{ Form::label('fecha_desde','Fecha inicial') }}
						{{ Form::date('fecha_desde',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_desde']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label('fecha_hasta','Fecha final') }}
						{{ Form::date('fecha_hasta',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_hasta']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label('grupo_cuenta_id','Grupo de cuentas') }}
						<br/>
						{{ Form::select('grupo_cuenta_id',$grupo_cuentas,null,['class'=>'combobox','id'=>'grupo_cuenta_id']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label('contab_cuenta_id','Cuenta') }}
						<br/>
						{{ Form::select('contab_cuenta_id',$cuentas,null,['class'=>'combobox','id'=>'contab_cuenta_id']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label('core_tercero_id','Tercero') }}
						<br/>
						{{ Form::select('core_tercero_id',$terceros,null,['class'=>'combobox','id'=>'core_terecero_id']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				</div>
				
			{{ Form::close() }}
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('auxiliar_por_cuenta') }}
			{{ Form::bsBtnPdf('auxiliar_por_cuenta') }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#fecha_desde').focus();

			$('#fecha_desde').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_hasta').focus();				
				}		
			});

			$('#fecha_hasta').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#mov_bodega_id').focus();				
				}		
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos()){
					alert('Debe diligenciar las fechas.');
					return false;
				}

				$('#resultado_consulta').html('');
				$('#div_cargando').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#resultado_consulta').html(respuesta);
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace('a3p0','contab_pdf_estados_de_cuentas?'+datos);
					}else{
						n = url_pdf.search('contab_pdf_estados_de_cuentas');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'contab_pdf_estados_de_cuentas?' + datos;
					}
					
					
					$('#btn_pdf').attr('href', new_url);
				});
			});

			function valida_campos(){
				var valida = true;
				if($('#fecha_desde').val()=='' || $('#fecha_hasta').val()=='' )
				{
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection