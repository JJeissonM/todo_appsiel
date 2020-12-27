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
	    opacity: .25;
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
	$anio = $request->anio;
    $curso_id = $request->curso_id;
    $id_formato = $request->formato_id;
    $tam_letra = $request->tam_letra;
    $tam_hoja = $request->tam_hoja;
    
    $colegio = App\Core\Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];
    
	use App\Http\Controllers\Core\ConfiguracionController;
	$secciones=DB::table('difo_secciones_formatos')->where('id_formato',$id_formato)->orderBy('orden','ASC')->get();

	// Listado de estudiantes del grado quinto en el año indicado
	$array_wheres = [
						['matriculas.curso_id', $curso_id],
						['matriculas.anio',$anio]
					]; 
	if ( isset($request->id_estudiante) )
	{
		$array_wheres += ['matriculas.id_estudiante',$request->id_estudiante];
	}

	$estudiantes = DB::table('matriculas')
		->join('sga_estudiantes', 'matriculas.id_estudiante', '=', 'sga_estudiantes.id')
		->select('matriculas.id', 'matriculas.id_estudiante', 'sga_estudiantes.nombres', 
				'sga_estudiantes.apellido1', 'sga_estudiantes.apellido2', 'matriculas.curso_id')
		->where( $array_wheres )
		->get();
			
	if(count($estudiantes)>0){
		
		// Seleccionar asignaturas del grado
		$asignaturas = App\Calificaciones\CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, $periodo_lectivo->id);
	}else{
		$estudiantes=0;
	}
	
	$url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;
?>

@foreach($estudiantes as $estudiante)
    
	<div style="font-size: {{$tam_letra}}mm; line-height: 1.5em;">
		<div class="watermark-{{$tam_hoja}}">
		    <img src="{{ $url }}"/>
		</div> 

		<div align="center" style="font-size: {{$tam_letra}}mm; line-height: 1em;">
			<b>{{ $colegio->descripcion }}</b><br/>
			<b style="padding-top: -10px;">Educación básica primaria</b><br/>
			Aprobado según resolución No. {{ $colegio->resolucion }}<br/>
			{{ $colegio->ciudad }}<br/>
		</div>

		@foreach($secciones as $una_seccion)
			<?php
			    
				$seccion = App\Core\DifoSeccion::find($una_seccion->id_seccion);
				$contenido = $seccion->contenido;
				
				$nombre_estudiante = $estudiante->nombres." ".$estudiante->apellido1." ".$estudiante->apellido2;
				$contenido = str_replace("nombre_de_estudiante", '<b>'.trim($nombre_estudiante).'</b>',$seccion->contenido);

				$contenido = str_replace("nombre_curso", '<b>'.App\Matriculas\Curso::find( $curso_id )->descripcion.'</b>', $contenido);

				$contenido = str_replace("nombre_del_colegio", $colegio->descripcion, $contenido);

				$contenido = str_replace("ciudad_colegio", $colegio->ciudad, $contenido);

				$contenido = str_replace("numero_dia_actual", date('d'), $contenido);

				$contenido = str_replace("numero_mes_actual", ConfiguracionController::nombre_mes(date('m')), $contenido);

				$contenido = str_replace("año_actual", date('Y'), $contenido);

				$contenido = str_replace("anio_ingresado", $anio, $contenido);
				
				if ($seccion->presentacion == 'tabla') 
		        {
		        
		            $estudiante = App\Matriculas\Estudiante::find( $estudiante->id_estudiante );

		            // Seleccionar asignaturas del grado
		            $asignaturas = App\Calificaciones\CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, $periodo_lectivo->id);
    
                    $periodos_promediar_aux = App\Calificaciones\Periodo::where('fecha_desde', 'LIKE', $anio.'-%' )->select('id')->get();
                    $periodos_promediar = [];
                    $lin = 0;
                    foreach ($periodos_promediar_aux as $linea)
                    {
                    	$periodos_promediar[$lin] = $linea->id;
                    	$lin++;
                    }
                    //dd($periodos_promediar);
		            $contenido.=View::make('core.dis_formatos.plantillas.tabla_asignaturas_calificacion',compact('asignaturas','colegio','anio','estudiante','curso_id','periodos_promediar'))->render();

		            $contenido = str_replace("_tabla_", "", $contenido);

		        }

				$espacios_antes = str_repeat("<br/>",$seccion->cantidad_espacios_antes);
				$espacios_despues = str_repeat("<br/>",$seccion->cantidad_espacios_despues);

				$estilos='text-align:'.$seccion->alineacion.';font-weight:'.$seccion->estilo_letra.';';
			    
			?>

			@include('core.dis_formatos.seccion',['presentacion'=>$seccion->presentacion,'contenido'=>$contenido,'espacios_antes'=>$espacios_antes,'estilos'=>$estilos,'espacios_despues'=>$espacios_despues,'asignaturas'=>$asignaturas,'colegio'=>$colegio,'anio'=>$anio,'id_periodo'=>0,'curso_id'=>$curso_id,'estudiante'=>$estudiante])


		@endforeach
	</div>

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
@endforeach