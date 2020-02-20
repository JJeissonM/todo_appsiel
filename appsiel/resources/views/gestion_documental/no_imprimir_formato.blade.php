@extends('layouts.principal')

<?php

	// Se obtienen los estudiantes con matriculas (cualquier año, cualquier curso)
	$estudiantes = DB::table('matriculas')
		->join('sga_estudiantes', 'matriculas.id_estudiante', '=', 'sga_estudiantes.id')
		->select('matriculas.codigo','matriculas.id_estudiante', 'sga_estudiantes.nombres', 
				'sga_estudiantes.apellido1', 'sga_estudiantes.apellido2', 'matriculas.curso_id')
		->where([['sga_estudiantes.id_colegio', Auth::user()->id_colegio]])
		->orderBy('sga_estudiantes.apellido1', 'ASC')
		->get();

	$firmas = App\Core\FirmaAutorizada::all();
	
	$opciones = App\Matriculas\Curso::all();    
    foreach ($opciones as $opcion){
    	$vec[$opcion->id]=$opcion->descripcion;
    }
    $cursos = $vec;
    unset($vec);

	$opciones = App\Calificaciones\Periodo::all();
    foreach ($opciones as $opcion){
        $vec[$opcion->id]=$opcion->descripcion;                      
    }
    $periodos = $vec;
    unset($vec);
?>

@section('content')
	<h3><i class="fa fa-file-text-o"></i> Gestión Documental</h3>
	<hr>

	@include('gestion_documental.menu_laretal')

	<div class="col-sm-offset-3 col-sm-6">
		<div class="panel panel-success">
			<div class="panel-heading" align="center">
				Impresión de <h4>{{$formato->descripcion}}</h4>
			</div>
			
			<div class="panel-body">
				{{Form::open(array('route'=>array('gestion_documental.update',$formato->id),'method'=>'PUT','class'=>'form-horizontal')) }}

					@if($formato->nota_mensaje!='')
					<div class="alert alert-warning">
					  <strong>Nota!</strong> {{ $formato->nota_mensaje}}
					</div>	
					@endif

					{{ Form::hidden('id_formato',$formato->id) }}

					@if($formato->maneja_anio=='Si')
						<div class="row" style="padding:5px;">
			                {{ Form::bsText('anio',date('Y'),"Año",[]) }}
			            </div>
						
					@endif

					@if($formato->maneja_periodo=='Si')
						<div class="row" style="padding:5px;">
			                {{ Form::bsSelect('id_periodo', $formato->periodo_predeterminado, 'Seleccionar periodo', $periodos ,[]) }}
			            </div>
						
					@endif

					@if($formato->maneja_curso=='Si')
						<div class="row" style="padding:5px;">
			                {{ Form::bsSelect('id_curso', $formato->curso_predeterminado, 'Seleccionar curso', $cursos ,[]) }}
			            </div>
						
					@endif

					@if($formato->maneja_estudiantes=='Si')
						<?php
							$vec['9999'] = '';
							foreach ($estudiantes as $opcion){
								$nombre_estudiante = $opcion->apellido1." ".$opcion->apellido2." ".$opcion->nombres;
								$nom_curso=DB::table('sga_cursos')->where('id',$opcion->curso_id)->value('descripcion');
								
								$vec[$opcion->id_estudiante.'-'.$opcion->curso_id]= $nombre_estudiante.' ('.$nom_curso.')';
							}
						?>
						<div class="row" style="padding:5px;">
			                {{ Form::bsSelect('id_estudiante',null, 'Estudiante', $vec ,[]) }}
			            </div>
					@endif

					@if($formato->maneja_firma_autorizada=='Si')
						<?php
							unset($vec);
							$vec['9999'] = '';
							foreach ($firmas as $opcion){
								$tercero = DB::table('core_terceros')->where('id',$opcion->core_tercero_id)->get();
									
								$nombre_tercero = $tercero[0]->nombre1." ".$tercero[0]->otros_nombres." ".$tercero[0]->apellido1." ".$tercero[0]->apellido2;
								
								$vec[$opcion->id]= $nombre_tercero.' ('.$opcion->titulo_tercero.')';
							}
						?>
						<div class="row" style="padding:5px;">
			                {{ Form::bsSelect('id_firma_autorizada',null, 'Firma autorizada', $vec ,[]) }}
			            </div>
					@endif

					<div class="row" style="padding:5px;">
		                {{ Form::bsSelect('tam_hoja',null,'Tamaño hoja',['letter'=>'Carta','legal'=>'Oficio'],[]) }}
		            </div>

					<div class="row" style="padding:5px;">
		                {{ Form::bsSelect('tam_letra','4.5','Tamaño Letra',['2'=>'10','2.5'=>'11','3'=>'12','3.5'=>'13','4'=>'14','4.5'=>'15','5'=>'16'],[]) }}
		            </div>

					<div class="row" style="padding:5px;">
		                {{ Form::bsSelect('orientacion','portrait','Orientación',['portrait'=>'Vertical','landscape'=>'Horizontal'],[]) }}
		            </div>

					<div class="row" style="padding:5px;" align="center">
		                {{ Form::bsButtonsForm('gestion_documental') }}
		            </div>    

				{{Form::close()}}
			</div>
		</div>
	</div>	
@endsection