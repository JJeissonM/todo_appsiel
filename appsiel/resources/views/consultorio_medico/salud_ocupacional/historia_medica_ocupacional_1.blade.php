<table class="table">
	<tr>
		<td width="20%">
			<img src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$empresa->imagen }}" height="{{ config('configuracion.alto_logo_formatos') }}" width="{{ config('configuracion.ancho_logo_formatos') }}" style="padding: 2px 10px;" />
		</td>
		<td>
			<div style="font-size: 15px; text-align: center;">
				<br/>
				<b>{{ $empresa->descripcion }}</b><br/>
				<b>{{ config("configuracion.tipo_identificador") }}: </b>
				@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} @endif - {{ $empresa->digito_verificacion }}</b><br/>
				{{ $empresa->direccion1 }}, {{ $empresa->ciudad->descripcion }} &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;Teléfono(s): {{ $empresa->telefono1 }}<br/>
				<b style="color: blue; font-weight: bold;">{{ $empresa->pagina_web }}</b><br/>
			</div>
		</td>
	</tr>
</table>
<br>
<h3 style="width: 100%; text-align: center; margin: -10px;">
	HISTORIA MÉDICA OCUPACIONAL
</h3>
<!-- Datos básicos del paciente -->
@include('consultorio_medico.salud_ocupacional.datos_paciente')

<!-- Datos básicos del Consulta 
@ include( 'consultorio_medico.consultas.datos_consulta' )-->


<br><br>

{!! $vistas_secciones !!}

<br/>

<footer>
	<hr>
	{{ $empresa->descripcion }}, Dirección: {{ $empresa->direccion1 }} Tel. {{ $empresa->telefono1 }}
</footer>