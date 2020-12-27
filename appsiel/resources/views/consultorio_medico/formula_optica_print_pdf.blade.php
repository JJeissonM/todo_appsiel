@extends('layouts.pdf')

@section('estilos_1')
	<style type="text/css">
		@page {
	            margin: 20px !important;
	        }
	</style>
		
@endsection

@section('content')

	<div class="row lbl_historia_clinica" style="border: solid 1px; font-size: 14px;" width="70%">
			
			@include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', ['vista'=>'imprimir'] )

			<table class="table table-bordered">
				<tr>
					<td>
						<b>Historia Clínica No.</b> {{ $datos_historia_clinica->codigo }}
					</td>
					<td colspan="2">
						<b>Fecha/Hora consulta: </b> {{ $consulta->fecha }} / {{ $consulta->created_at->format('h:i:s a') }}
					</td>
					<td>
						@if( !is_null( $formula ) )
							<b>Fórmula No. </b> <span class="formula_id">{{ $formula->id }}</span>
						@else
							<b>Fórmula No. </b> <span class="formula_id"> XXXXXXXX </span>
						@endif
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<b>Paciente:</b> {{ $datos_historia_clinica->nombres }} {{ $datos_historia_clinica->apellidos }}
					</td>
					<td>
						<b>Identificación:</b> {{ number_format( $datos_historia_clinica->numero_identificacion, 0, ',', '.' ) }}
					</td>
					<td>
						<b>Edad:</b> {{ \Carbon\Carbon::parse($datos_historia_clinica->fecha_nacimiento)->diff(\Carbon\Carbon::now())->format('%y años') }}
					</td>
				</tr>
			</table>

			{!! $examenes !!}
			
			<p style="text-align: right;">

				<?php

					$firma = "<br>_________________________________";

					if ( !is_null($firma_autorizada) ) 
					{
						if ( $firma_autorizada->imagen != "") 
						{
							$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada->imagen;	
							$firma = '<img src="'.$url_firma.'" width="250px" height="70px" style="margin-bottom: -20px;"/>';
						}						
					}	
				?>
				{!! $firma !!} <br>
				{{ $profesional_salud->nombre_completo }} <br>
				{{ $profesional_salud->especialidad }} <br>
				{{ $profesional_salud->numero_carnet_licencia }}
			</p>
	</div>
@endsection