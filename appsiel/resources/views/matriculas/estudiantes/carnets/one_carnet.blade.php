
<table class="carnet_table">
	<tr>
		<td>
			<p class="descripcion_colegio"> 
				{{ $estudiante->colegio->descripcion }}
			</p>
			<p class="slogan_colegio">
				{!! $estudiante->colegio->slogan !!}
			</p>
		</td>
		<td rowspan="4" style="width: 130px; text-align:center; padding:0;">
			<?php
				$src = asset( 'assets/images/avatar.png/' );
				if ( $estudiante->imagen != '' && $imagen_mostrar == 'foto_estudiante')
				{
					$src = asset( config('configuracion.url_instancia_cliente').'/storage/app/fotos_terceros/'.$estudiante->imagen );
				}else{
					$src = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/' . $estudiante->colegio->imagen;
				}
			?>
			<img alt="foto.jpg" src="{{ $src }}" style="width: 100px; height: 110px; margin-left:-20px;" />
		</td>
	</tr>
	<tr>
		<td style="text-align: center; background-color:lightgrey"><strong>CARNET ESTUDIANTIL </strong> </td>
	</tr>
	<tr>
		<td><p class="celda"><strong>NOMBRES: </strong> {{ $estudiante->nombre1 }} {{ $estudiante->otros_nombres }}</p></td>		
	</tr>
	<tr>
		<td><p class="celda"><strong>APELLIDOS: </strong> {{ $estudiante->apellido1 }} {{ $estudiante->apellido2 }}</td>		
	</tr>
	<tr>
		<td><p class="celda">
			<strong>Doc. Identidad: </strong> 
			<!-- <br> -->
			{{ $estudiante->estudiante->tercero->tipo_doc_identidad->abreviatura }} {{ number_format($estudiante->estudiante->tercero->numero_identificacion, 0,',','.') }}</p>
		</td>
		<td><p class="celda">
			<strong>{{ config('calificaciones.etiqueta_curso') }}:</strong>
			<!-- <br> -->
			{{ $curso->descripcion }} </p>
		</td>
	</tr>
	<tr>
		<td colspan="2"><p class="celda">
			<strong>{{ $estudiante->colegio->piefirma1 }}:</strong>
			{{ $estudiante->colegio->empresa->representante_legal() }} </p>
		</td>
	</tr>
</table>