@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			{{ Form::open(['url'=>'cxc_ajax_estados_de_cuentas','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-md-10">
						<div class="row">
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsFecha( 'fecha_final', date('Y-m-d'), 'Fecha corte', '', [ 'id' => 'fecha_final' ] ) }}

									{{ Form::hidden( 'fecha_inicial', date('1900-m-d') ) }}
					            </div>
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect( 'estado', null, 'Estado documentos', [ '' => 'Todos', 'Vencido' => 'Vencido', 'Pagado' => 'Pagado', 'Pendiente' => 'Pendiente' ], [ 'id' => 'estado' ] ) }}
					            </div>
					        </div>
							<div class="col-md-6">
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect( 'codigo_referencia_tercero', null, 'Propiedad', $propiedades, [ 'class' => 'combobox', 'id' => 'codigo_referencia_tercero' ] ) }}
					            </div>
								<div class="row" style="padding:5px; display: none;">
									{{ Form::bsSelect( 'core_tercero_id', null, 'Tercero', $terceros, [ 'class' => 'combobox', 'id' => 'core_tercero_id' ] ) }}
					            </div>
								<div class="row" style="padding:5px;">
									{{ Form::bsSelect( 'tipo_informe', null, 'Tipo informe', ['detallado'=>'Detallado','resumido'=>'Resumido'], [ 'id' => 'tipo_informe' ] ) }}
					            </div>
					        </div>
					    </div>
			        </div>

					<div class="col-md-2">
						<div class="row" style="padding:5px;">
							<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
			            </div>
			        </div>
				</div>
			{{ Form::close() }}
			 <button id="btn_ir">ir</button><!--	-->
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			{{ Form::bsBtnExcel('estado_de_cuentas') }}
			{{ Form::bsBtnPdf('estado_de_cuentas') }}
			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#fecha_inicial').focus();

			$('#fecha_inicial').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_final').focus();				
				}		
			});

			$('#fecha_final').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#mov_bodega_id').focus();				
				}		
			});

			$('#contab_grupo_cuenta_id').change(function(){
				if ($('#contab_grupo_cuenta_id').val()!='') {
					$('#contab_cuenta_id').focus();
				}
			});

			$('#contab_cuenta_id').change(function(){
				if ($('#contab_cuenta_id').val()!='') {
					$('#btn_generar').focus();
				}
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				
				if(!valida_campos()){
					alert('Debe diligencias todos los campos.');
					return false;
				}
				
				$('#resultado_consulta').html( '' );
				$('#btn_excel').hide();
				$('#btn_pdf').hide();

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
						var new_url = url_pdf.replace('a3p0','cxc_pdf_estados_de_cuentas?'+datos);
					}else{
						n = url_pdf.search('cxc_pdf_estados_de_cuentas');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'cxc_pdf_estados_de_cuentas?' + datos;
					}
					
					
					$('#btn_pdf').attr('href', new_url);

					//alert( $('#btn_pdf').attr('href') );
				});
			});

			function valida_campos(){
				var valida = true;
				if( $('#fecha_inicial').val()=='' || $('#fecha_final').val()=='' ){
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection