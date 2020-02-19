@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		body {
			background-color: #FAFAFA !important;
        }
	</style>
@endsection


@section('content')
	
	<h1>Anuncios</h1>
		<?php			
			$cant_cols=3; // Cantidad de columnas 
			$i=$cant_cols;
	      ?>
	    @foreach($anuncios as $fila)
	          
	          @if($i % $cant_cols == 0)
	            <div class="row">
	          @endif
	          
	          <?php

	          		$tam_icono = '100px';

	          		$url_imagen = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/anuncios/'.$fila->imagen;

	          		$enlace_web = $fila->enlace_web;
	          ?>
		        <div class="col-sm-{{12/$cant_cols}}" style="border: 1px solid gray; margin: 5px;">
		          	<p style="font-weight: bold; font-size: 1.1em; color: #007BB6; margin-bottom: -25PX;" >{{ $fila->descripcion }}</p>
		          	<hr>

		          	<div class="row">
		          			@if( $fila->detalle !='' )
				          		<div class="col-sm-6">
				          			<img class="img-responsive" src="{{ $url_imagen }}" style="height: 150px; width: 150px;" />
				          		</div>
				          		<div class="col-sm-6">
				          			<p>{{ $fila->detalle }}</p>
		          				</div>
				          	@else
				          		<?php
									$tam_icono = '100%';
								?>
				          		<div class="col-sm-12">
				          			<img class="img-responsive" src="{{ $url_imagen }}"  style="height: 150px; width: 100%;" />
				          		</div>
				          	@endif
		          	</div>
		          	<div class="row">
		          		<div class="col-sm-4">
		          			@if( $fila->enlace_web != '' )
		          				<a href="{{ $fila->enlace_web }}" target="_blank"> <img class="img-responsive" src="https://upload.wikimedia.org/wikipedia/commons/7/75/2016_Web.png" width="40px" /> </a>
		          			@endif
		          		</div>
		          		<div class="col-sm-4">
		          			@if( $fila->enlace_facebook != '' )
		          				<a href="{{ $fila->enlace_facebook }}" target="_blank"> <img class="img-responsive" src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Facebook_icon.svg/256px-Facebook_icon.svg.png" width="40px" /> </a>
		          			@endif
		          		</div>
		          		<div class="col-sm-4">
		          			@if( $fila->enlace_instagram != '' )
		          				<a href="{{ $fila->enlace_instagram }}" target="_blank"> <img class="img-responsive" src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d8/Instagram_Shiny_Icon.svg/256px-Instagram_Shiny_Icon.svg.png" width="40px" /> </a>
		          			@endif
		          		</div>
		          	</div>
		        </div>
	    	<?php
	          $i++;
	      	?>
	          @if($i % $cant_cols == 0)
	          	<!-- Por cada cantidad de columnas definidas ($cant_cols ) se cierra la fila -->
	            </div>

	            <?php			
					$i=$cant_cols;
			      ?>

	          @endif
	    @endforeach

@endsection