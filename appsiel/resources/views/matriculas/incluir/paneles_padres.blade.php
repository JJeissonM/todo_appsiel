<div class="container-fluid well">
    <div class="col-sm-6">
        <div class="row" style="padding:5px;">
            <div class="form-group">
                    {{ Form::bsSelect('acudiente_seleccionado', null, '<i class="fa fa-asterisk"></i>¿Quién es el acudiente?', [''=>'', 'padre'=>'El padre', 'madre'=>'La madre','otro'=>'Otro'], ['class'=>'form-control','required'=>'required']) }}
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        &nbsp;
    </div>
    <br>
    <br>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-success">
            <div class="panel-heading">Datos del padre</div>
            <div class="panel-body">

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('cedula_papa', null, 'Cédula', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('papa', null, 'Nombre Padre', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('direccion_papa', null, 'Dirección', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('telefono_papa', null, 'Teléfono', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('email_papa', null, 'Email', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('ocupacion_papa', null, 'Ocupación', []) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="panel panel-primary">
            <div class="panel-heading">Datos de la madre</div>
            <div class="panel-body">

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('cedula_mama', null, 'Cédula', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('mama', null, 'Nombre Madre', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('direccion_mama', null, 'Dirección', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('telefono_mama', null, 'Teléfono', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('email_mama', null, 'Email', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('ocupacion_mama', null, 'Ocupación', []) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="div_acudiente" style="display: none;">
    <div class="col-sm-6">
        <div class="panel panel-info">
            <div class="panel-heading">Datos del acudiente</div>
            <div class="panel-body">

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('cedula_acudiente', null, 'Cédula', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('acudiente', null, 'Nombre Acudiente', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('direccion_acudiente', null, 'Dirección', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('telefono_acudiente', null, 'Teléfono', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('email_acudiente', null, 'Email', []) }}
                </div>

                <div class="row" style="padding:5px;">
                    {{ Form::bsText('ocupacion_acudiente', null, 'Ocupación', []) }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        &nbsp;
    </div>
</div>