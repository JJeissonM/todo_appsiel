<?php

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
?>
<table border="0" width="100%">
	<tr>
		<td width="20%">
			<img src="{{ $url }}" height="{{ config('configuracion.alto_logo_formatos') }}" width="{{ config('configuracion.ancho_logo_formatos') }}" style="padding: 2px 10px;" />
		</td>
		<td>
			<div style="font-size: 15px; text-align: center;">
				<br/>
				<b>{{ $empresa->descripcion }}</b><br/>
				<b>NIT. {{ number_format($empresa->numero_identificacion, 0, ',', '.') }} - {{ $empresa->digito_verificacion }}</b><br/>
				{{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
				Teléfono(s): {{ $empresa->telefono1 }}<br/>
				<b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
			</div>
		</td>
	</tr>
</table>