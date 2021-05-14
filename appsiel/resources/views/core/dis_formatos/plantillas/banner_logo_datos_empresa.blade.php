<?php

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen; 	 

    $ciudad = DB::table('core_ciudades')->where('id',$empresa->codigo_ciudad)->get()[0];
?>
<table border="0" width="100%">
	<tr>
		<td width="30%" align="center">
			<!--<img src="{{ $url }}" height="{{ config('configuracion.alto_logo_formatos') }}" width="{{ config('configuracion.ancho_logo_formatos') }}" style="padding: 2px 10px;" />-->
			<p style="text-align: center">
			<?php
			
			$image = getimagesize($url);
			$ancho = $image[0];            
 			$alto = $image[1];   
			 

			 if($ancho >= $alto ){
				$pancho = (160*100)/$ancho;
				$alto = $alto*$pancho/100;				
				if($alto > 120){
					$ancho = 160;
					$palto = (120*100)/$alto;
					$ancho = $ancho*$palto/100;
					echo '<img src="'.$url.'" width="'.$ancho.'" height="120" style="margin-left: 10px" />';
				}else{
					echo '<img src="'.$url.'" height="'.$alto.'" width="160" style="margin-left: 10px" />';
				}
			 }else{
				$palto = (120*100)/$alto;
				$ancho = $ancho*$palto/100;
				echo '<img src="'.$url.'" width="'.$ancho.'" height="120" style="margin-left: 10px" />';
			 }

			?>	
			</p>
			
				
		</td>
		<td>
			<div style="font-size: 15px; text-align: center;">
				<br/>
				<b>{{ $empresa->descripcion }}</b><br/>
				<b>{{ config("configuracion.tipo_identificador") }}:
					@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
				{{ $empresa->direccion1 }}, {{ $ciudad->descripcion }} <br/>
				Teléfono(s): {{ $empresa->telefono1 }}<br/>
				<b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
			</div>
		</td>
	</tr>
</table>