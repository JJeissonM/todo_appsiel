@extends('layouts.principal')

@section('content')
	
	{{ Form::bsMigaPan($miga_pan) }}
	
	<div class="row">
		<div class="col-md-4">
			<div class="btn-group">
				
				@if( isset($url_crear) )
					{{ Form::bsBtnCreate( $url_crear ) }}
				@endif

				@yield('botones_acciones')
				
			</div>
		</div>

		<div class="col-md-4 text-center">
			<div class="btn-group">
				@yield('botones_imprimir_email') 
			</div>			
		</div>

		<div class="col-md-4">	
			<div class="btn-group pull-right">
				@yield('botones_anterior_siguiente')
			</div>			
		</div>	

	</div>
	<hr>

	@include('layouts.mensajes')

	@yield('div_advertencia_anulacion')

	<div class="container-fluid">
		<div class="marco_formulario">

			<br><br>
			<div class="table-responsive">
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

			                    @yield('datos_adicionales_encabezado')
			                    
			                </div>
			                @if($doc_encabezado->estado == 'Anulado')
			                    <div class="alert alert-danger" class="center">
			                        <strong>Documento Anulado</strong>
			                    </div>
			                @endif
			            </td>
			        </tr>
			        @yield('filas_adicionales_encabezado')
			    </table>


			    @yield('encabezado2')
			</div>

		    <!-- se usa yield porque es una estructura particular para la vista de cada transacción -->
		    @yield('documento_vista')
		    
			{!! $documento_vista !!}
			
			<br>

			<!-- se usa include porque es la misma estructura para la vista de todas las transacciones -->
		    @include('transaccion.registros_contables')

		    <!-- se usa yield porque es una estructura particular para la vista de cada transacción -->
		    @yield('registros_otros_documentos')

			@include('transaccion.auditoria')

		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')

	@yield('otros_scripts')

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

			$('#formato_impresion_id').on('change',function(){
				var btn_print = $('#btn_print').attr('href');

				n = btn_print.search('formato_impresion_id');
				var url_aux = btn_print.substr(0,n);
				var new_url = url_aux + 'formato_impresion_id=' + $(this).val();
				
				$('#btn_print').attr('href', new_url);



				var btn_email = $('#btn_email').attr('href');

				n = btn_email.search('formato_impresion_id');
				var url_aux = btn_email.substr(0,n);
				var new_url = url_aux + 'formato_impresion_id=' + $(this).val();
				
				$('#btn_email').attr('href', new_url);
				
			});

		});
	</script>
@endsection