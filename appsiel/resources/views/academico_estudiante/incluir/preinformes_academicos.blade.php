<div class="well" style="background-color: #d2d2d2; padding: 10px; border-radius: 5px;">
	
	<h3>Consultar preinformes</h3>
	<hr>
	<?php 
		$modelo_padre_id = App\Sistema\Modelo::where('modelo', 'periodos')->value('id');
		$core_campo_id = 749; // Visualizar preinforme

		$opciones = App\Core\ModeloEavValor::where(
                                            [ 
                                                "modelo_padre_id" => $modelo_padre_id,
                                                "core_campo_id" => $core_campo_id,
                                                "valor" => 1
                                            ]
                                        )
                                    ->get();
        $vec[0]='';
        foreach ($opciones as $opcion)
        {
        	$el_periodo = App\Calificaciones\Periodo::find( $opcion->registro_modelo_padre_id );
        	$periodo_lectivo = App\Matriculas\PeriodoLectivo::find( $el_periodo->periodo_lectivo_id );

            $vec[$el_periodo->id] = $periodo_lectivo->descripcion . ' > ' . $el_periodo->descripcion;
        }

        $periodos_visualizar = $vec;

	?>

	<div class="row">
			<div class="col-sm-4">
				{{ Form::label('periodo_visualizar_id','Seleccionar periodo') }}
				{{ Form::select('periodo_visualizar_id',$periodos_visualizar,null,['class'=>'form-control','id'=>'periodo_visualizar_id']) }}
			</div>
			<div class="col-sm-">
				<br>
				<button class="btn btn-primary btn-sm" id="btn_consultar_preinforme" data-periodo_visualizar_id="0" data-curso_id="{{$curso->id}}" data-estudiante_id="{{ $estudiante->id }}">Consultar</button>
			</div>
			@include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
	</div>
</div>