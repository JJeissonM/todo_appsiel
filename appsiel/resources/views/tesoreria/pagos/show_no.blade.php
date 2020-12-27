@extends('layouts.principal')

@section('content')
	
	{{ Form::bsMigaPan($miga_pan) }}
	
	<div class="row">
		<div class="col-md-4">
			{{ Form::bsBtnCreate( 'tesoreria/pagos/create'.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion ) }}
		</div>

		<div class="col-md-4 text-center">
			<div class="btn-group">
				{{ Form::bsBtnPrint( 'tesoreria/pagos_print/'.$id ) }}
				{{ Form::bsBtnEmail( 'tesoreria/pagos_enviar_por_email/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}  
			</div>			
		</div>

		<div class="col-md-4">	
			<div class="btn-group pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev( 'tesoreria/pagos/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext( 'tesoreria/pagos/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
				@endif
			</div>			
		</div>	

	</div>
	<hr>

	@include('layouts.mensajes')

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

		});
	</script>
@endsection