@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		body {
			background-color: #FAFAFA !important;
        }
	</style>
@endsection


@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>
	
	<div class="container col-sm-8 col-sm-offset-2">
		
		<br/><br/><br/>
		<input id="myInput" type="text" placeholder="Buscar..." style="border: none; border-color: transparent; background-color: transparent; border-bottom: 1px solid #ddd; width: 100%;">
		<br/>
		<div id="myDIV">
			<?php

				
				$cant_cols=4;
				$i=$cant_cols;
		      ?>
		        @foreach($permisos as $fila)
		          
		          @if($i % $cant_cols == 0)
		            <div class="row">
		          @endif
		          
		          <?php
		          		$url=$fila['url'].'?id='.Input::get('id').'&id_modelo='.$fila['modelo_id'];
		          ?>
		          @can($fila['name'])
			          <div class="col-sm-{{12/$cant_cols}}">
			          		<a href="{{url($url)}}">
			          			<h1><i class="fa fa-{{$fila['fa_icon']}}"></i></h1>
				                {{$fila['descripcion']}}
				            </a>
			          </div>
			     @endcan
		    <?php
		          $i++;

		          $modelo_tercero_a_cliente_id = 157;

		      ?>
		          @if($i % $cant_cols == 0)
		            </div>
		            <br/>
		          @endif
		        @endforeach

		        <div class="col-sm-{{12/$cant_cols}}">
	          		<a href="{{ url('vtas_clientes_tercero_a_cliente_create'.'?id='.Input::get('id').'&id_modelo='.$modelo_tercero_a_cliente_id )}}">
	          			<h1><i class="fa fa-child"></i></h1>
		                Crear tercero como cliente
		            </a>
	          </div>
		</div>
	</div>
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