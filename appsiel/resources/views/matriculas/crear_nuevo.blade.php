<?php

use App\Http\Controllers\Sistema\VistaController;
?>

@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')
<div class="container-fluid">
    <div class="marco_formulario">

        @include('matriculas.incluir.matriculas_anteriores')

        <br /><br />
        <form action="{{ url('matriculas') }}" method="POST" class="form-horizontal" id="form_create">
            {{ csrf_field() }}

            <input type="hidden" name="id_colegio" id="id_colegio" value="{{ $id_colegio }}">

            @include('matriculas.incluir.datos_inscripcion')

            <div class="panel panel-primary">
                <div class="panel-heading">DATOS DE LA MATRÍCULA</div>
                <div class="panel-body">
                    {{ VistaController::campos_dos_colummnas($form_create['campos']) }}
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">Requisitos de matrícula</div>
                    <div class="panel-body">
                        <table class="fluid" width="100%">
                            <tr>
                                <td><input type="checkbox" name="requisito1" id="matricular"> Documento identidad</td>
                                <td><input type="checkbox" name="requisito2" id="matricular"> Constancia SIMAT</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="requisito3" id="matricular"> Fotos</td>
                                <td><input type="checkbox" name="requisito4" id="matricular"> Registro calificaciones</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="requisito5" id="matricular"> Carnet EPS</td>
                                <td><input type="checkbox" name="requisito6" id="matricular"> Registro de vacunación</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            @if( !$estudiante_existe )
            <!--@include('matriculas.incluir.paneles_padres')-->

            <div class="panel panel-primary">
                <div class="panel-heading">DATOS DE PADRES, ACUDIENTE, RESPONSABLE FINANCIERO, ETC.</div>
                <div class="panel-body">
                    <div class="alert alert-warning" role="alert">
                        <h4><b>Atención! </b>Debe como mínimo incluir al acudiente y al responsable financiero, de no ser así la matrícula no será registrada y tendrá que realizar el procedimiento de nuevo</h4>
                    </div>
                    <a onclick="addRow()" class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> Agregar Persona</a>
                    <table id="tbPersonas" class="table table-hover table-dark table-responsive">
                        <thead>
                            <tr>
                                <th scope="col">Tipo Doc.</th>
                                <th scope="col">Número Doc.</th>
                                <th scope="col">Primer Nombre</th>
                                <th scope="col">Segundo Nombre</th>
                                <th scope="col">Primer Apellido</th>
                                <th scope="col">Segundo Apellido</th>
                                <th scope="col">Ocupación</th>
                                <th scope="col">Teléfono</th>
                                <th scope="col">Correo</th>
                                <th scope="col">Tipo</th>
                                <th scope="col"><i class="fa fa-remove"></i></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Controles médicos</div>
                        <div class="panel-body">
                            <div class="row" style="padding:5px;">
                                {{ Form::bsText('grupo_sanguineo', null, 'Grupo sanguíneo', []) }}
                            </div>

                            <div class="row" style="padding:5px;">
                                {{ Form::bsText('medicamentos', null, 'Medicamento', []) }}
                            </div>

                            <div class="row" style="padding:5px;">
                                {{ Form::bsText('alergias', null, 'Alergias', []) }}
                            </div>

                            <div class="row" style="padding:5px;">
                                {{ Form::bsText('eps', null, 'EPS', []) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    &nbsp;
                </div>
            </div>
            @endif

            <div align="center">

                {{ Form::hidden('estudiante_existe', $estudiante_existe) }}

                {{ Form::hidden('core_tercero_id', $tercero->id) }}
                {{ Form::hidden('nombre1', $tercero->nombre1) }}
                {{ Form::hidden('otros_nombres', $tercero->otros_nombres) }}
                {{ Form::hidden('apellido1', $tercero->apellido1) }}
                {{ Form::hidden('apellido2', $tercero->apellido2) }}
                {{ Form::hidden('email', $tercero->email) }}
                {{ Form::hidden('nombres', $tercero->nombre1." ".$tercero->otros_nombres) }}
                {{ Form::hidden('tipo_doc_id', $tercero->id_tipo_documento_id) }}
                {{ Form::hidden('doc_identidad', $tercero->numero_identificacion) }}
                {{ Form::hidden('direccion1', $tercero->direccion1) }}
                {{ Form::hidden('telefono1', $tercero->telefono1) }}

                {{ Form::hidden('genero',$inscripcion->genero) }}

                {{ Form::hidden('fecha_nacimiento',$inscripcion->fecha_nacimiento) }}
                {{ Form::hidden('ciudad_nacimiento',$inscripcion->ciudad_nacimiento) }}
                {{ Form::hidden('estado','Activo') }}
                {{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
                {{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}

                {{ Form::bsButtonsForm($miga_pan[count($miga_pan)-2]['url'])}}
            </div>
        </form>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">DATOS RESPONSABLE FINANCIERO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ponerID">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Dirección Trabajo*</label>
                            <input type="text" class="form-control" id="direccion_trabajo" value=" " required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Teléfono Trabajo*</label>
                            <input type="text" class="form-control" id="telefono_trabajo" value=" " required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Puesto Trabajo</label>
                            <input type="text" class="form-control" id="puesto_trabajo">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Empresa Dónde Labora</label>
                            <input type="text" class="form-control" id="empresa_labora">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Jefe Inmediato</label>
                            <input type="text" class="form-control" id="jefe_inmediato">
                        </div>
                        <div class="form-group">
                            <label class="control-label">Teléfono Jefe</label>
                            <input type="text" class="form-control" id="telefono_jefe">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">Si Es Trabajador Independiente Escriba su Actividad</label>
                            <input type="text" class="form-control" id="descripcion_trabajador_independiente">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <h4>Presione Guardar Datos para terminar el proceso, la ventana se cerrará</h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="button" onclick="guardar()" class="btn btn-primary">Guardar Datos</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script type="text/javascript">
    $(document).on('click', '.delete', function(event) {
        event.preventDefault();
        $(this).closest('tr').remove();
    });



    $(document).ready(function() {

        $('#bs_boton_guardar').on('click', function(event) {
            event.preventDefault();

            if (!validar_requeridos()) {
                return false;
            }
            // Desactivar el click del botón
            $(this).off(event);

            $('#form_create').submit();
        });


        $('#sga_grado_id').focus();

        if (typeof $('#btn_imprimir') !== 'undefined') {
            $('#btn_imprimir').focus();
        }



        $('#sga_grado_id').change(function() {
            var grado = $('#sga_grado_id').val().split('-');

            var codigo = $('#codigo').val();

            $('#codigo').val(codigo.replace(codigo.substr(codigo.search("-")), '-' + grado[1]));
        });
    });

    var tipos = <?php echo json_encode($tipos); ?>;
    var tiposdoc = <?php echo json_encode($tiposdoc); ?>;

    var io = 0;

    function addRow() {
        var html = "<tr>";
        var selectTiposdoc = "<select class='form-control' required name='id_tipo_documento_idp[]'>";
        tiposdoc.forEach(element => {
            selectTiposdoc = selectTiposdoc + "<option value='" + element.id + "'>" + element.descripcion + "</option>";
        });
        html = html + "<td>" + selectTiposdoc + "</select></td>";
        html = html + "<td><input type='text' class='form-control' name='numero_docp[]' required /></td>";
        html = html + "<td><input type='text' class='form-control' name='nombre1p[]' required /></td>";
        html = html + "<td><input type='text' class='form-control' name='otros_nombresp[]' required /></td>";
        html = html + "<td><input type='text' class='form-control' name='apellido1p[]' required /></td>";
        html = html + "<td><input type='text' class='form-control' name='apellido2p[]' required /></td>";
        html = html + "<td><input type='text' class='form-control' name='ocupacionp[]' required /></td>";
        html = html + "<td><input type='number' class='form-control' name='telefono1p[]' required /></td>";
        html = html + "<td><input type='email' class='form-control' name='emailp[]' required /></td>";
        io = io + 1;
        var selectTipos = "<select class='form-control' required name='tiporesponsable_idp[]' onchange='cambiar(this.id)' id='" + io + "'>";
        tipos.forEach(element => {
            selectTipos = selectTipos + "<option value='" + element.id + "'>" + element.descripcion + "</option>";
        });
        html = html + "<td>" + selectTipos + "</select><input type='hidden' id='hidden_" + io + "' name='datosp[]'></td>";
        html = html + "<td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
        html = html + "</tr>";
        $('#tbPersonas tr:last').after(html);
    }

    function cambiar(id) {
        var rf = $("#" + id + " option:selected").text();
        if (rf == 'RESPONSABLE-FINANCIERO') {
            $("#exampleModal").modal('show');
            $("#ponerID").val(id);
        }
    }

    function guardar() {
        var id = $("#ponerID").val();
        //recojo la informacion y la coloco en el input hidden del row
        var html = $("#direccion_trabajo").val() + ";" + $("#telefono_trabajo").val() + ";" + $("#puesto_trabajo").val();
        html = html + ";" + $("#empresa_labora").val() + ";" + $("#jefe_inmediato").val() + ";" + $("#telefono_jefe").val();
        html = html + ";" + $("#descripcion_trabajador_independiente").val()
        $("#hidden_" + id).val(html);
        $("#exampleModal").modal('hide');
    }
</script>
@endsection