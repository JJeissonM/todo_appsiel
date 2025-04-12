<div class="marco_formulario">
    <h4 style="color: gray;">Ingreso de calificaciones</h4>
    <hr>
    {{ Form::open( array('url'=>'/academico_docente/calificar_desempenios/calificar2?id='.Input::get('id'), 'id'=>'form_filtros' ) ) }}

        <div class="row">

            <div class="col-md-3">
                <div class="row" style="padding:5px;">
                    {{ Form::bsSelect('curso_id', $curso_id, 'Curso', $cursos, ['required'=>'required']) }}
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="row" style="padding:5px;">
                    {{Form::bsSelect('id_asignatura', $asignatura_id, 'Asignatura', $asignaturas, ['required'=>'required'])}}
                </div>
            </div>

            <div class="col-md-3">
                <div class="row" style="padding:5px;">
                    {{ Form::bsSelect('id_periodo','','Periodo',$periodos,['required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary" id="btn_continuar">
                    <i class="fa fa-btn fa-arrow-right"></i>Continuar
                </button>
            </div>
        </div>

        {{ Form::hidden('id_app', Input::get('id')) }}
        {{ Form::hidden('periodo_lectivo_id', $periodo_lectivo_id, [ 'id' => 'periodo_lectivo_id' ]) }}

    {{ Form::close() }}
</div>