<?php

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
    $compactar_banner = isset($tamano_letra) || isset($alto_logo) || isset($ancho_logo);
    $tamano_letra = $tamano_letra ?? 15;
    $alto_logo = $alto_logo ?? null;
    $ancho_logo = $ancho_logo ?? null;
?>
<table style="border: 0px; width:100%;">
	<tr>
		<td width="30%" style="text-align: center">
			@include('core.dis_formatos.plantillas.render_logo_empresa', ['url' => $url, 'alto_logo' => $alto_logo, 'ancho_logo' => $ancho_logo])
		</td>
		<td>
			<div style="font-size: {{ $tamano_letra }}px; text-align: center;{{ $compactar_banner ? ' line-height: 1.08;' : '' }}">
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
