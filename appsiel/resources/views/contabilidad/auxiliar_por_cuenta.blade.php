@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading" style="background-color:#3c8dbc;color:#fff;">
					<i class="fa fa-filter"></i> Filtros del auxiliar por cuenta
					<span class="pull-right"><small>Seleccione el periodo y los criterios de consulta</small></span>
				</div>
				<div class="panel-body" style="padding-bottom:5px;">
					{{ Form::open(['url'=>'contab_ajax_auxiliar_por_cuenta','id'=>'form_consulta']) }}
					<div class="row">
						<div class="col-xs-12">
							<h5 style="border-bottom:1px solid #eee;padding-bottom:5px;margin-top:0;color:#3c8dbc;">
								<i class="fa fa-calendar"></i> Periodo
							</h5>
						</div>
						<div class="col-xs-6 col-sm-3 col-md-2">
							<div class="form-group">
								{{ Form::label('fecha_desde','Fecha inicial') }}
								{{ Form::date('fecha_desde',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_desde']) }}
							</div>
						</div>
						<div class="col-xs-6 col-sm-3 col-md-2">
							<div class="form-group">
								{{ Form::label('fecha_hasta','Fecha final') }}
								{{ Form::date('fecha_hasta',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_hasta']) }}
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<h5 style="border-bottom:1px solid #eee;padding-bottom:5px;color:#3c8dbc;">
								<i class="fa fa-book"></i> Cuentas contables
							</h5>
							<p class="help-block" style="margin-top:-5px;">Debe ingresar al menos una Clase, un Grupo, una Cuenta o un Tercero.</p>
						</div>
						<div class="col-xs-12 col-sm-4 col-md-3">
							<div class="form-group">
								{{ Form::label('clase_cuenta_id','Clase de cuentas') }}
								{{ Form::select('clase_cuenta_id',$clases_cuentas,null,['class'=>'form-control','id'=>'clase_cuenta_id']) }}
							</div>
						</div>
						<div class="col-xs-12 col-sm-4 col-md-3">
							<div class="form-group">
								{{ Form::label('grupo_cuenta_id','Grupo de cuentas') }}
								{{ Form::select('grupo_cuenta_id',$grupo_cuentas,null,['class'=>'form-control combobox','id'=>'grupo_cuenta_id']) }}
							</div>
						</div>
						<div class="col-xs-12 col-sm-4 col-md-3">
							<div class="form-group">
								{{ Form::label('contab_cuenta_id','Cuenta') }}
								{{ Form::select('contab_cuenta_id',$cuentas,null,['class'=>'form-control combobox','id'=>'contab_cuenta_id']) }}
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-3">
							<div class="form-group">
								{{ Form::label('core_tercero_id','Tercero') }}
								{{ Form::select('core_tercero_id',$terceros,null,['class'=>'form-control combobox','id'=>'core_terecero_id']) }}
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<h5 style="border-bottom:1px solid #eee;padding-bottom:5px;color:#3c8dbc;">
								<i class="fa fa-cogs"></i> Opciones del reporte
							</h5>
						</div>
						<div class="col-xs-6 col-sm-4 col-md-3">
							<div class="form-group">
								{{ Form::label('agrupar_por_cuenta','Agrupar por cuenta') }}
								{{ Form::select('agrupar_por_cuenta',['0'=>'No','1'=>'Sí'],null,['class'=>'form-control','id'=>'agrupar_por_cuenta']) }}
								<p class="help-block">Sí: ordena, agrupa y subtotaliza por cada cuenta.</p>
							</div>
						</div>
						<div class="col-xs-6 col-sm-4 col-md-3">
							<div class="form-group">
								<label>&nbsp;</label>
								<button type="submit" class="btn btn-primary btn-block" id="btn_generar"><i class="fa fa-play"></i> Generar</button>
							</div>
						</div>
					</div>
						
					{{ Form::close() }}
				</div>
			</div>
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid" style="margin-top:15px;">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('auxiliar_por_cuenta') }}
			{{ Form::bsBtnPdf('auxiliar_por_cuenta') }}

			<div id="auxiliar_cargando" class="text-center" role="status" aria-live="polite" style="display:none;padding:20px;">
                <i class="fa fa-spinner fa-spin fa-2x" aria-hidden="true"></i>
                <p>Generando auxiliar por cuenta…</p>
            </div>
            <div id="resultado_consulta" aria-busy="false">

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
				
			$('#clase_cuenta_id').on('change', function(){
				if( $(this).val() == '' )
				{
					$('#grupo_cuenta_id').next('.custom-combobox').fadeIn(500);
					$('#contab_cuenta_id').next('.custom-combobox').fadeIn(500);
				}else{
					$('#grupo_cuenta_id').next('.custom-combobox').hide();
					$('#contab_cuenta_id').next('.custom-combobox').hide();
				}
			});

			var generando = false;
			var texto_generar = $('#btn_generar').html();
			$('#form_consulta').on('submit', function(event){
                event.preventDefault();
                if (generando) { return; }
				if(!valida_campos()){
					alert('Debe diligenciar las fechas.');
					return false;
				}

                generando = true;
                $('#btn_generar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Generando…');
                $('#btn_excel, #btn_pdf').hide();
                $('#resultado_consulta').html('').attr('aria-busy', 'true');
                $('#auxiliar_cargando').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
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
                }).fail(function () {
                    $('#resultado_consulta').html('<div class="alert alert-danger" role="alert">No fue posible generar el auxiliar. Intente nuevamente.</div>');
                }).always(function () {
                    generando = false;
                    $('#auxiliar_cargando').hide();
                    $('#resultado_consulta').attr('aria-busy', 'false');
                    $('#btn_generar').prop('disabled', false).html(texto_generar);
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