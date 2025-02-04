<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">Controles médicos</div>
            <div class="panel-body">
                <div class="row" style="padding:5px;">
                    {{ Form::bsText('grupo_sanguineo', $estudiante->grupo_sanguineo, 'Grupo sanguíneo', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('medicamentos', $estudiante->medicamentos, 'Medicamento', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('alergias', $estudiante->alergias, 'Alergias', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('eps', $estudiante->eps, 'EPS', []) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        &nbsp;
    </div>
</div>