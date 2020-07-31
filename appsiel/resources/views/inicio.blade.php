@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		body {
			background-color: #FAFAFA !important;
        }

        .img-responsive:hover {
	        transform: scale(1.1);
	        cursor: pointer;
	    }

	</style>
@endsection


@section('content')
	
	<div class="container col-sm-6 col-sm-offset-3">

		<div class="row" align="center">
			@include('banner')
		</div>

		@include('layouts.mensajes')

		<input id="myInput" type="text" placeholder="Buscar..." style="border: none; border-color: transparent; background-color: transparent; border-bottom: 1px solid #ddd; width: 100%;">
		<br/><br/>
		<div id="myDIV">
			<?php
				//echo __DIR__;
				/*
					Se muestran las aplicaciones a las que el usuario tiene permiso.
					El nombre del permiso (permissions.name) debe coincidir con el nombre de la aplicación (sys_aplicaciones.descripcion) para poder ser mostrada.
				*/
				$cant_cols=4;
				$tam_iconos = '80px';
				$i=$cant_cols;
		      ?>
		        @foreach($aplicaciones as $fila)
		          @if($i % $cant_cols == 0)
		            <div class="row">
		          @endif
		          <?php
		          	$url=$fila['app'].'?id='.$fila['id'];
		          ?>
		          @can($fila['descripcion'])
			          <div class="col-sm-{{12/$cant_cols}} col-xs-{{12/$cant_cols}}" style="padding: 5px; text-align: center;">
			          		<a href="{{url($url)}}">
			          			<img class="img-responsive" src="https://appsiel.com.co/el_software/assets/img/{{$fila['nombre_imagen']}}" width="{{$tam_iconos}}" title="{{$fila['descripcion']}}" style="display: inline;" />
			          			<p>
			          				{{$fila['descripcion']}}
			          			</p>
				                
				            </a>
			          </div>
			      @endcan
				    <?php
				          $i++;
				      ?>
		          @if($i % $cant_cols == 0)
		            </div>
		            <br/>
		          @endif
		        @endforeach
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$("#myInput").focus();	
		  $("#myInput").on("keyup", function() {
		    var value = $(this).val().toLowerCase();
		    $("#myDIV *").filter(function() {
		      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		    });
		  });
		});
	</script>
@endsection