<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-success">
            <div class="panel-heading">Datos del padre</div>
            <div class="panel-body">

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('cedula_papa', null, 'Cédula', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('papa', null, 'Nombre', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('ocupacion_papa', null, 'Ocupación', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('telefono_papa', null, 'Teléfono', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('email_papa', null, 'Email', []) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="panel panel-warning">
            <div class="panel-heading">Datos de la madre</div>
            <div class="panel-body">

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('cedula_mama', null, 'Cédula', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('mama', null, 'Nombre', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('ocupacion_mama', null, 'Ocupación', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('telefono_mama', null, 'Teléfono', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('email_mama', null, 'Email', []) }}
                </div>
            </div>
        </div>
    </div>
</div>