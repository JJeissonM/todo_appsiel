<div class="marco_formulario">
    <h4 style="color: gray;">Ingreso de calificaciones</h4>
    <hr>
    {{ Form::open( array('url'=>'/calificaciones/calificar2?id='.Input::get('id'), 'id'=>'form_filtros' ) ) }}

        <div class="row">

            <div class="col-md-3">
                <div class="row" style="padding:5px;">
                    {{ Form::bsSelect('curso_id', $curso_id, 'Curso', $cursos, ['required'=>'required']) }}
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="row" style="padding:5px;">
                    {{Form::bsSelect('id_asignatura', $asignatura_id, 'Asignatura', $asignaturas, ['required'=>'required'])}}
                    <div id="spinner_asignaturas" style="display: none; padding-top: 4px; color: #777;">
                        <i class="fa fa-spinner fa-spin"></i> Cargando asignaturas...
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="row" style="padding:5px;">
                    {{ Form::bsSelect('id_periodo','','Periodo',$periodos,['required'=>'required']) }}
                </div>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary" id="btn_continuar">
                    <span class="lbl_btn_continuar">
                        <i class="fa fa-btn fa-arrow-right"></i>Continuar
                    </span>
                    <span class="spinner_btn_continuar" style="display: none;">
                        <i class="fa fa-spinner fa-spin"></i> Cargando...
                    </span>
                </button>
            </div>
        </div>

        {{ Form::hidden('id_app', Input::get('id')) }}
        {{ Form::hidden('periodo_lectivo_id', $periodo_lectivo_id, [ 'id' => 'periodo_lectivo_id' ]) }}

    {{ Form::close() }}
</div>
