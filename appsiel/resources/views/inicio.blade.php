@extends('layouts.principal')

@section('estilos_1')
<style type="text/css">
	/*body {
			background-color: #FAFAFA !important;
        }*/

	body {
		background-position: bottom;
		background-attachment: fixed;
		background-size: cover;
		background-image: url({{asset('assets/img/fondo-colegio.jpeg')}})
	}

	.img-responsive:hover {
		transform: scale(1.2) rotate(-15deg);
		cursor: pointer;
	}

	.banner {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.nombre-empresa {
		display: flex;
		justify-content: center;
		font-size: calc(1em + 3vw);
		width: 550px;
	}

	#div_contenido {
		/*background-image: url(https://cdn.pixabay.com/photo/2016/08/31/17/02/blue-1634110_960_720.png);
		    background-repeat: no-repeat;
		    width: 100%;
		    background-attachment: fixed;
		    margin-top: -25px;*/
		/*background: url(https://cdn.pixabay.com/photo/2017/07/01/19/48/background-2462428_960_720.jpg) no-repeat center center fixed; 
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
			width: 100%;*/
		/*{ {asset('assets/images/fondo_inicio.jpg')}}*/
	}

	#paula {
		right: 10px;
		bottom: 70px;
		position: fixed;
		display: none;
	}

	#btnPaula {
		right: 20px;
		bottom: 15px;
		position: fixed;
		z-index: 1000;
	}

	.paula {
		background-color: #fff;
		width: 300px;
		height: auto;
		text-align: center;
		-webkit-box-shadow: -10px 10px 10px 0px rgba(0,0,0,0.41);
		-moz-box-shadow: -10px 10px 10px 0px rgba(0,0,0,0.41);
		box-shadow: -10px 10px 10px 0px rgba(0,0,0,0.41);
	}
</style>
@endsection


@section('content')
<div id="div_contenido">

	<div class="container col-sm-6 col-sm-offset-3">

		<div class="row" align="center">
			@include('banner')
		</div>

		@include('layouts.mensajes')


		<input id="myInput" type="text" placeholder="Buscar..." class="form-control">
		<br /><br />
		<div id="myDIV">
			<?php
			//echo __DIR__;
			/*
						Se muestran las aplicaciones a las que el usuario tiene permiso.
						El nombre del permiso (permissions.name) debe coincidir con el nombre de la aplicación (sys_aplicaciones.descripcion) para poder ser mostrada.
					*/
			$cant_cols = 4;
			$tam_iconos = '100px';
			$i = $cant_cols;
			?>
			@foreach($aplicaciones as $fila)
			@if($i % $cant_cols == 0)
			<div class="row">
				@endif
				<?php
				$url = $fila['app'] . '?id=' . $fila['id'];
				?>
				@can($fila['descripcion'])
				<div class="col-sm-{{12/$cant_cols}} col-xs-{{12/$cant_cols}}" style="padding: 5px; text-align: center;">
					<a href="{{url($url)}}">
						<img class="img-responsive" src="https://appsiel.com.co/el_software/assets/iconos_2021/{{$fila['nombre_imagen']}}" width="{{$tam_iconos}}" title="{{$fila['descripcion']}}" style="display: inline;" />
						<p style="color: #000;">
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
			<br />
			@endif
			@endforeach
		</div>
	</div>
	<div id="paula" class="paula">
		<div class="col-md-12">
			<img width="220px" height="350px" src="{{asset('assets/images/ayuda.png')}}" />
		</div>
		<div class="col-md-12" style="margin-bottom: 20px; margin-top: 20px;">
			<a class="btn btn-block btn-default"><i class="fa fa-arrow-right"></i> Tutoriales en Video</a>
		</div>
	</div>
	<div id="btnPaula">
		<button onclick="paula()" style="border-radius: 50%;" class="btn btn-danger">¿Ayuda?</button>
	</div>
	<!--<div id="paula">
			<img width="230px" height="350px" src="{{asset('assets/images/ayuda.png')}}" />
		</div>-->
</div>
<br /><br />
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$("#myInput").focus();
		$("#myInput").on("keyup", function() {
			var value = $(this).val().toLowerCase();
			$("#myDIV *").filter(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});
		});
	});

	var verPaula = true;

	function paula(){
		if(verPaula){
			//ver paula
			$("#btnPaula").html("<button class='btn btn-danger' style='border-radius: 50%;' onclick='paula()'>Ocultar Paula</button>");
			$("#paula").fadeIn();
			verPaula = false;
		}else{
			//ocultar paula
			$("#btnPaula").html("<button class='btn btn-danger' style='border-radius: 50%;' onclick='paula()'>¿Ayuda?</button>");
			$("#paula").fadeOut();
			verPaula = true;
		}	
	} 
</script>
@endsection