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

    .watermark-letter {
	    position: fixed;
	    top: 7%;
	    text-align: center;
	    opacity: .2;
	    z-index: -1000;
	  }

    .watermark-folio {
	    position: fixed;
	    top: 15%;
	    text-align: center;
	    opacity: .2;
	    z-index: -1000;
	  }

	.escudo img{
		display:block;
		margin:auto;
		width: 95%;
	}

 </style>

<?php    
    $colegio = App\Core\Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();

	$url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;
?>

@foreach($estudiantes as $estudiante)
    
	<div style="font-size: 17px;">
		
		<div class="watermark-{{$tam_hoja}} escudo">
		    <img src="{{ $url }}"/>
		</div> 

		<div align="center" style="font-size: 1.1em;">
			<b>{{ $colegio->descripcion }}</b><br/>
			<b style="padding-top: -10px;">Educación básica primaria</b><br/>
			Aprobado según resolución No. {{ $colegio->resolucion }}<br/>
			{{ $colegio->ciudad }}<br/>
		</div>

		<br><br>

		<div align="center">
			EL DIRECTOR(A) GENERAL DE {{ $colegio->descripcion }}
		</div>
		
		<br>

		<div align="center">
			CERTIFICA QUE:
		</div>


		<br>


		<div align="center" style="font-weight: bold;">
			{{ $estudiante->nombre_completo }}
		</div>

		<br>

		<div style="text-align: justify;">
			Cursó en esta institución educativa el grado <b>{{ $curso->grado->descripcion }}</b>, según pensum oficial. Habiendo obtenido en el {{ $periodo_lectivo->descripcion }} las calificaciones que a continuación se registran:
		</div>		

		<br>

		<div class="container-fluid">
			<div class="row">
				{!! View::make( 'core.dis_formatos.plantillas.tabla_asignaturas_calificacion_2', compact( 'asignaturas','colegio','estudiante','curso', 'periodo_id' ) )->render() !!}
			</div>
		</div>

		<br>


		<div style="text-align: justify;">
			Observaciones: APROBÓ( &nbsp;&nbsp; )  &nbsp;&nbsp;&nbsp;&nbsp;    REPROBÓ( &nbsp;&nbsp; )    &nbsp;&nbsp;&nbsp;&nbsp;    APLAZÓ( &nbsp;&nbsp; )
			<br>
			{{ $observacion_adicional }}
		</div>

		
		
		<br><br>

		<div style="text-align: justify;">
			Para mayor constancia, se firma la presente en la ciudad de {{ $colegio->ciudad }} a los {{ $array_fecha[0] }} días del mes de {{ $array_fecha[1] }} de {{ $array_fecha[2] }}.
		</div>


		<br><br>
	</div>

	<table border="0" style="font-size: 0.8em;">
		<tr>
			<td width="150px"> &nbsp; </td>
			<td align="center">	_____________________________ </td>
			<td align="center"> &nbsp;	</td>
			<td align="center">	_____________________________ </td>
			<td width="50px">&nbsp;</td>
		</tr>
		<tr>
			<td width="150px"> &nbsp; </td>
			<td align="center">	
				{{ $firma_autorizada_1->tercero_nombre }} 
				<br>
				{{ $firma_autorizada_1->tercero_tipo_doc_identidad }} {{ number_format( $firma_autorizada_1->tercero_numero_identificacion, 0, ',', '.' ) }}
				<br>
				{{ $firma_autorizada_1->tercero_titulo }} 
			</td>
			<td align="center"> &nbsp;	</td>
			<td align="center">	
				{{ $firma_autorizada_2->tercero_nombre }} 
				<br>
				{{ $firma_autorizada_2->tercero_tipo_doc_identidad }} {{ number_format( $firma_autorizada_2->tercero_numero_identificacion, 0, ',', '.' ) }}
				<br>
				{{ $firma_autorizada_2->tercero_titulo }} 
			</td>
			<td width="50px">&nbsp;</td>
		</tr>
	</table>
	<div class="page-break"></div>
@endforeach