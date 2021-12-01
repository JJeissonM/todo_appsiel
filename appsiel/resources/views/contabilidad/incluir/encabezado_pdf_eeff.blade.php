<?php
    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
?>
<div class="table-responsive">
	<table border="0" style="margin-top: 12px !important;" width="100%">
		<tr>
			<td width="20%">
				<img src="{{ $url }}" height="{{ config('configuracion.alto_logo_formatos') }}" />
			</td>
			<td>
				<div style="font-size: 15px; text-align: center; font-weight: bold;">
					{{ $empresa->descripcion }}
				</div>
				<div style="font-size: 13px; text-align: center; font-weight: bold;">
					{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}
				</div>
				<div style="font-size: 15px; text-align: center; font-weight: bold;">
					{{ $titulo }}
				</div>
			</td>
		</tr>
	</table>
</div>