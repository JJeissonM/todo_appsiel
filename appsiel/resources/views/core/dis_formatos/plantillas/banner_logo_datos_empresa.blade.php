<?php

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
?>
<table style="border: 0px; width:100%;">
	<tr>
		<td width="30%" style="text-align: center">
			@include('core.dis_formatos.plantillas.render_logo_empresa', ['url' => $url])
		</td>
		<td>
			<div style="font-size: 15px; text-align: center;">
				<b>{!! $empresa->descripcion !!}</b><br/>
				<b>{{ config("configuracion.tipo_identificador") }}:
					@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
				{!! $empresa->direccion1 !!}, {!! $ciudad->descripcion !!} <br/>
				Teléfono(s): {!! $empresa->telefono1 !!}<br/>
				<b style="color: blue; font-weight: bold;">{!! $empresa->pagina_web !!}</b><br/>
			</div>
		</td>
	</tr>
</table>