<style>

	img {
		padding-left:30px;
	}

	
	.page-break {
		page-break-after: always;
	}
</style>

<style>
    @page { margin: 100px 25px; }
    header { 
    	position: fixed; 
    	top: -60px; 
    	left: 0px; 
    	right: 0px; 
    	background-color: lightblue; 
    	height: 50px; 
    }

    footer { 
    	position: fixed; 
    	bottom: -70px; 
    	left: 0px; 
    	right: 0px; 
    	background-color: lightblue; 
    	height: 40px;
    	text-align: center;
    }

    p { page-break-after: always; }
    p:last-child { page-break-after: never; }

    #watermark-letter {
	    position: fixed;
	    top: 7%;/**/
	    width: 100%;
	    text-align: center;
	    opacity: .3;
	    /*transform: rotate(10deg);*/
	    transform-origin: 50% 50%;
	    z-index: -1000;
	  }

    #watermark-legal {
	    position: fixed;
	    top: 15%;/**/
	    width: 100%;
	    text-align: center;
	    opacity: .3;
	    /*transform: rotate(10deg);*/
	    transform-origin: 50% 50%;
	    z-index: -1000;
	  }

 </style>
<?php
	$secciones=DB::table('difo_secciones_formatos')->where('id_formato',$request->id_formato)->orderBy('orden','ASC')->get();
?>

<div id="watermark-{{$request->tam_hoja}}">
    <img src="{{ asset(config('configuracion.url_instancia_cliente').'/storage/app/escudos/escudo_'.$colegio->id.'.jpg?'.rand(1,1000)) }}"/>
</div>

<div style="font-size: {{$request->tam_letra}}mm; line-height: 1.5em;">
	@if(count($secciones)>0)
		@foreach($secciones as $una_seccion)
			@php 
				$seccion = App\Core\DifoSeccion::find($una_seccion->id_seccion);
			@endphp

			@include('core.dis_formatos.mostrar_seccion',['seccion'=>$seccion,'request'=>$request])
		@endforeach
	@else

	@endif
</div>

<footer>
	<b>{{ $colegio->descripcion }} - {{ $colegio->slogan }}</b>. Resolución No. {{ $colegio->resolucion }}<br/>
	{{ $colegio->direccion }}, Teléfonos: {{ $colegio->telefonos }}<br/>
</footer>