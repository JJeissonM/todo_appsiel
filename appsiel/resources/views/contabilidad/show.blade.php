<?php  
	$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
?>

@extends('layouts.principal')

@section('content')
	
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-4">
			<div class="btn-group">
				
				{{ Form::bsBtnCreate( 'contabilidad/create'.$variables_url ) }}

				@if( $doc_encabezado->estado != 'Anulado' )
					{{ Form::bsBtnEdit2( 'contabilidad/'.$id.'/edit'.$variables_url,'Editar') }}

				    <button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-btn fa-close"></i> Anular </button>
				@endif
				
			</div>
		</div>

		<div class="col-md-4 text-center">
			<div class="btn-group">
				{{ Form::bsBtnPrint( 'contabilidad_print/'.$id ) }}
			</div>			
		</div>

		<div class="col-md-4">	
			<div class="btn-group pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev( 'contabilidad/'.$reg_anterior.$variables_url ) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext( 'contabilidad/'.$reg_siguiente.$variables_url ) }}
				@endif
			</div>			
		</div>	

	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">


			<div class="alert alert-warning" style="display: none;">
				<a href="#" id="close" class="close">&times;</a>
				<strong>Advertencia!</strong>
				<br>
				Al anular el documento se eliminan los registros del movimiento contable. La anulación no se puede revertir.
				<br>
				Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url( 'contab_anular_documento/'.$id.$variables_url ) }}"> Anular </a> </small>
			</div>

			<br><br>

			<table class="table table-bordered">
		        <tr>
		            <td width="50%" style="border: solid 1px #ddd; margin-top: -40px;">
		                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
		            </td>
		            <td style="border: solid 1px #ddd; padding-top: -20px;">
		                <div style="vertical-align: center;">
		                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
		                    <br/>
		                    <b>Documento:</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
		                    <br/>
		                    <b>Fecha:</b> {{ $doc_encabezado->fecha }}
		                    
		                </div>
		                @if($doc_encabezado->estado == 'Anulado')
		                    <div class="alert alert-danger" class="center">
		                        <strong>Documento Anulado</strong>
		                    </div>
		                @endif
		            </td>
		        </tr>
		        <tr>
			        <td style="border: solid 1px #ddd;">
			            <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
			            <br/>
			            <b>Documento ID: &nbsp;&nbsp;</b> {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
			        </td>
			    </tr>
			    <tr>        
			        <td colspan="2" style="border: solid 1px #ddd;">
			            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
			        </td>
			    </tr>
		    </table>

			{!! $view_pdf !!}
			
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
		});

	</script>
@endsection