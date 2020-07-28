<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
?>

@extends('layouts.principal')

@section('content')
	
	{{ Form::bsMigaPan($miga_pan) }}
	
	<div class="row">
		<div class="col-md-4">
			<div class="btn-group">
				{{ Form::bsBtnCreate( 'tesoreria/recaudos_cxc/create'.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion ) }}
				@if($doc_encabezado->estado != 'Anulado')
	                <button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-btn fa-close"></i> Anular </button>
	            @endif
	        </div>
		</div>

		<div class="col-md-4 text-center">
			<div class="btn-group">
				{{ Form::bsBtnPrint( 'tesoreria_recaudos_cxc_imprimir/'.$id.$variables_url ) }}
				{{ Form::bsBtnEmail( 'tesoreria_recaudos_cxc_enviar_por_email/'.$id.$variables_url ) }}  
			</div>			
		</div>

		<div class="col-md-4">	
			<div class="btn-group pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev( 'tesoreria/recaudos_cxc/'.$reg_anterior.$variables_url ) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext( 'tesoreria/recaudos_cxc/'.$reg_siguiente.$variables_url ) }}
				@endif
			</div>			
		</div>	

	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>Advertencia!</strong>
		<br>
		Al anular el documento se eliminan los registros de abonos a las Cuentas por Cobrar y el movimiento contable relacionado.
		<br>
		Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url('teso_anular_recaudo_cxc/'.$id.$variables_url ) }}"> Anular </a> </small>
	</div>

	<div class="container-fluid">
		<div class="marco_formulario">

			<br><br>

			{!! $documento_vista !!}
			
			<br><br>

		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#btn_print').focus();

			$('#btn_print').animate( {  borderSpacing: 45 }, {
			    step: function(now,fx) {
			      $(this).css('-webkit-transform','rotate('+now+'deg)'); 
			      $(this).css('-moz-transform','rotate('+now+'deg)');
			      $(this).css('transform','rotate('+now+'deg)');
			    },
			    duration:'slow'
			},'linear');

			$('#btn_print').animate({  borderSpacing: 0 }, {
			    step: function(now,fx) {
			      $(this).css('-webkit-transform','rotate('+now+'deg)'); 
			      $(this).css('-moz-transform','rotate('+now+'deg)');
			      $(this).css('transform','rotate('+now+'deg)');
			    },
			    duration:'slow'
			},'linear');

			$('#btn_anular').on('click',function(e){
				e.preventDefault();
				$('.alert.alert-warning').show(1000);
			});

			$('#close').on('click',function(e){
				e.preventDefault();
				$('.alert.alert-warning').hide(1000);
			});

		});
	</script>
@endsection