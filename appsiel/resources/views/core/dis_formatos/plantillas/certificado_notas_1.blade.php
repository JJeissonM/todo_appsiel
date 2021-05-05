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
	    top: 7%;/**/
	    width: 100%;
	    text-align: center;
	    opacity: .3;
	    /*transform: rotate(10deg);*/
	    transform-origin: 50% 50%;
	    z-index: -1000;
	  }

    .watermark-legal {
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

	use App\Http\Controllers\Core\DisFormatosController;

	use App\Core\Colegio;

	use App\Matriculas\Estudiante;
	use App\Matriculas\PeriodoLectivo;
	use App\Calificaciones\Periodo;

	$secciones=DB::table('difo_secciones_formatos')->where('id_formato',$formato->id)->orderBy('orden','ASC')->get();

	$empresa = App\Core\Empresa::find(Auth::user()->empresa_id);

	$colegio = Colegio::where('empresa_id','=', $empresa->id )
                    ->get()[0];

    // El certificado puede ser generado para uno o todos los estudiantes del curso

    $vec_estudiante = explode("-",$request->id_estudiante);

	// Si no se escogió un estudiante en particular
    if ( $vec_estudiante[0] == '')
    {
    	// Listado de estudiantes, Con matriculas activas e inactivas
		$estudiantes = App\Matriculas\Matricula::estudiantes_matriculados( $request->curso_id, PeriodoLectivo::get_actual()->id, null );
    }else{

    	// Se llama a un solo estudiante
    	$estudiante = Estudiante::where('id', $vec_estudiante[0] )->get()[0];
    }
	
?>

	<div style="font-size: 13mm; line-height: 1.5em;">
		
		@include('banner_colegio')

		@foreach($secciones as $una_seccion)
			<?php

				$contenido = "";
				$seccion = App\Core\DifoSeccion::find($una_seccion->id_seccion);
				
				// Se reemplazan las palabras claves (campos) que tenga la seccion en su contenido
				$contenido.=DisFormatosController::formatear_contenido($request, $seccion, $estudiante);

				$espacios_antes = str_repeat("<br/>",$seccion->cantidad_espacios_antes);
				$espacios_despues = str_repeat("<br/>",$seccion->cantidad_espacios_despues);

				$estilos='text-align:'.$seccion->alineacion.';font-weight:'.$seccion->estilo_letra.';';
				
			?>

			@include('core.dis_formatos.seccion',['presentacion'=>$seccion->presentacion,'contenido'=>$contenido,'espacios_antes'=>$espacios_antes,'estilos'=>$estilos,'espacios_despues'=>$espacios_despues])


		@endforeach
	</div>
	<br/>

	<table border="0">
		<tr>
			<td width="150px"> &nbsp; </td>
			<td align="center">	_____________________________ </td>
			<td align="center"> &nbsp;	</td>
			<td align="center">	_____________________________ </td>
			<td width="50px">&nbsp;</td>
		</tr>
		<tr>
			<td width="150px"> &nbsp; </td>
			<td align="center">	{{ $colegio->piefirma1 }} </td>
			<td align="center"> &nbsp;	</td>
			<td align="center">	{{ $colegio->piefirma2 }} </td>
			<td width="50px">&nbsp;</td>
		</tr>
	</table>
	<div class="page-break"></div>