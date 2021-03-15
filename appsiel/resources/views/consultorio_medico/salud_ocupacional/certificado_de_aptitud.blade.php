<table class="table" style="border: none !important;">
	<tr style="border: none !important;">
		<td width="20%">
			<img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen }}" height="{{ config('configuracion.alto_logo_formatos') }}" width="{{ config('configuracion.ancho_logo_formatos') }}" style="padding: 2px 10px;" />
		</td>
		<td>
			<div style="font-size: 15px; text-align: center;">
				<br/>
				<b>{{ $empresa->descripcion }}</b><br/>
				<b>NIT. {{ number_format($empresa->numero_identificacion, 0, ',', '.') }} - {{ $empresa->digito_verificacion }}</b><br/>
				{{ $empresa->direccion1 }}, {{ $empresa->ciudad->descripcion }} &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;Teléfono(s): {{ $empresa->telefono1 }}<br/>
				<b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
			</div>
		</td>
	</tr>
</table>
<br>
<h3 style="width: 100%; text-align: center; margin: -10px;">
	CERTIFICADO DE APTITUD
</h3>
<!-- Datos básicos del paciente -->
@include('consultorio_medico.salud_ocupacional.datos_paciente_aptitud')

<br><br>

{!! $vistas_secciones !!}
