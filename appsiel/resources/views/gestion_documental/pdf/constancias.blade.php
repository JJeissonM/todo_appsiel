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



<div id="watermark-{{$tam_hoja}}">
    <img src="{{ asset(config('configuracion.url_instancia_cliente').'/storage/app/escudos/escudo_'.$colegio->id.'.jpg?'.rand(1,1000)) }}"/>
</div>  

<?php
	use App\Http\Controllers\Core\ConfiguracionController;
	$secciones=DB::table('difo_secciones_formatos')->where('id_formato',$id_formato)->orderBy('orden','ASC')->get();
?>

<div style="font-size: {{$tam_letra}}mm; line-height: 1.5em;">
	@foreach($secciones as $una_seccion)
		<?php 
			$contenido = "";
			$seccion = App\Core\DifoSeccion::find($una_seccion->id_seccion);
			
			$nombre_estudiante = $estudiante->apellido1.' '.$estudiante->apellido2.' '.$estudiante->nombres;

			$contenido = str_replace("nombre_de_estudiante", '<b>'.trim($nombre_estudiante).'</b>',$seccion->contenido);

			$contenido = str_replace("nombre_curso", '<b>'.$curso->descripcion.'</b>', $contenido);

			$contenido = str_replace("ciudad_colegio", $colegio->ciudad, $contenido);

			$contenido = str_replace("numero_dia_actual", date('d'), $contenido);

			$contenido = str_replace("numero_mes_actual", ConfiguracionController::nombre_mes(date('m')), $contenido);

			$contenido = str_replace("año_actual", date('Y'), $contenido);

			$firma_autorizada = DB::table('core_firmas_autorizadas')->where('id',$id_firma_autorizada)->get();

			//explode("-",$id_firma_autorizada);
			$tercero = DB::table('core_terceros')->where('id',$firma_autorizada[0]->core_tercero_id)->get();

			$tipo_doc_id = DB::table('core_tipos_docs_id')->where('id',$tercero[0]->id_tipo_documento_id)->value('abreviatura');

			$nombre_tercero = $tercero[0]->nombre1." ".$tercero[0]->otros_nombres." ".$tercero[0]->apellido1." ".$tercero[0]->apellido2;
			$contenido = str_replace("nombre_tercero", $nombre_tercero, $contenido);

			$contenido = str_replace("nueva_linea","<br/>", $contenido);

			$contenido = str_replace("tipo_documento_tercero", $tipo_doc_id, $contenido);

			$contenido = str_replace("numero_documento_tercero", number_format($tercero[0]->numero_identificacion, 0, ',', '.'), $contenido);

			$contenido = str_replace("ciudad_expedicion_documento", $tercero[0]->ciudad_expedicion, $contenido);

			$contenido = str_replace("titulo_tercero", $firma_autorizada[0]->titulo_tercero, $contenido);

			$espacios_antes = str_repeat("<br/>",$seccion->cantidad_espacios_antes);
			$espacios_despues = str_repeat("<br/>",$seccion->cantidad_espacios_despues);

			$estilos='text-align:'.$seccion->alineacion.';font-weight:'.$seccion->estilo_letra.';';
		?>

		@include('core.dis_formatos.seccion',['presentacion'=>$seccion->presentacion,'contenido'=>$contenido,'espacios_antes'=>$espacios_antes,'estilos'=>$estilos,'espacios_despues'=>$espacios_despues])
	@endforeach
</div>

<footer style="font-size: 4mm; line-height: 1.1em">
	<b>{{ $colegio->descripcion }} - {{ $colegio->slogan }}</b>. Resolución No. {{ $colegio->resolucion }}<br/>
	{{ $colegio->direccion }}, Teléfonos: {{ $colegio->telefonos }}<br/>
</footer>