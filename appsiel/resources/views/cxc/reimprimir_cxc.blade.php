@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			{{ Form::open(['url'=>'cxc/ajax_reimprimir_cxc','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-3">
						{{ Form::label('core_tipo_doc_app_id','Tipo documento') }}
						{{ Form::select('core_tipo_doc_app_id',$tipos_documentos,null,[ 'class' => 'form-control','id'=>'core_tipo_doc_app_id']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('consecutivo_desde','Consecutivo desde') }}
						{{ Form::text('consecutivo_desde',null,[ 'class' => 'form-control','id'=>'consecutivo_desde','required'=>'required']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('consecutivo_hasta','Consecutivo hasta') }}
						{{ Form::text('consecutivo_hasta',null,[ 'class' => 'form-control','id'=>'consecutivo_hasta','required'=>'required']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				</div>

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				
				<!--	<button id="btn_ir">ir</button>	-->

			{{ Form::close() }}
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<br/><br/>

			<div id="btn_imprimir_lote" class="pull-right" style="display: none;">
				{{ Form::bsBtnPrint('') }}
			</div>
			<div id="btn_enviar_email_lote" class="pull-right" style="display: none;">
				{{ Form::bsBtnEmail('') }}
				<br/><br/>
			</div>

			<h3 id="lbl_tabla" align="center"></h3>
		    <table class="table table-striped table-bordered" id="ingreso_cxc">
		        <thead>
		        </thead>
		        <tbody>
		        </tbody>
		    </table>			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			var enlace = $('#btn_imprimir_lote').find('a').attr('href');
			var enlace2 = $('#btn_enviar_email_lote').find('a').attr('href');

			$('#btn_generar').click(function(e){
				e.preventDefault();

				$('#ingreso_cxc').find('thead').html('');
				$('#ingreso_cxc').find('tbody').html('');

				if (validar_requeridos()) 
				{
						
					var form = $('#form_consulta');
					var url = form.attr('action');
					data = form.serialize();
					$.post(url,data,function(resultado)
					{		
						$('#ingreso_cxc').find('thead').html(resultado[0]);
						$('#ingreso_cxc').find('tbody').html(resultado[1]);
						
						$('#btn_imprimir_lote').show(1000);
						
						$('#btn_imprimir_lote').find('a').attr('href',enlace+'/cxc/imprimir_lote/'+resultado[5]+'/'+resultado[6]+'/'+resultado[7]+'/'+resultado[8]);
						
						$('#btn_enviar_email_lote').show(1000);
						
						$('#btn_enviar_email_lote').find('a').attr('href',enlace2+'/cxc/enviar_email_lote/'+resultado[5]+'/'+resultado[6]+'/'+resultado[7]+'/'+resultado[8]);
					});
				}else{
					alert('Faltan campos por llenar.');
				}								
			});

			function validar_requeridos()
			{
				var control = true;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = false;
					  alert('Este campo es requerido.');
					  return false;
					}else{
					  control = true;
					}
				});
				return control;
			}
		});
	</script>
@endsection