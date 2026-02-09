@extends('core.procesos.layout')

@section('titulo', 'Actualizar sueldo en contratos')

@section('detalles')
    <p>
        Este proceso permite actualizar el sueldo de contratos activos aplicando un porcentaje de aumento.
    </p>
@endsection

@section('formulario')
    <div class="row" id="div_formulario">
        <div class="col-md-12">
            <div class="marco_formulario">
                <div class="container-fluid">
                    <h4>
                        Parámetros de selección
                    </h4>
                    <hr>
                    {{ Form::open(['url'=>'nomina/procesos/actualizar_sueldos/preview','id'=>'formulario_inicial','files' => false]) }}
                        <div class="row" style="padding:5px;">
                            <label class="control-label col-sm-4"> <b>*Grupo de empleados:</b> </label>
                            <div class="col-sm-8">
                                <?php
                                    $opciones = App\Nomina\GrupoEmpleado::opciones_campo_select();
                                    $opciones = ['0' => 'Todos'] + $opciones;
                                ?>
                                {{ Form::select('grupo_empleado_id', $opciones, '0', [ 'class' => 'form-control', 'id' => 'grupo_empleado_id' ]) }}
                            </div>
                        </div>
                        <div class="row" style="padding:5px;">
                            <label class="control-label col-sm-4"> <b>*Porcentaje de aumento:</b> </label>
                            <div class="col-sm-8">
                                {{ Form::number('porcentaje_aumento', null, [ 'class' => 'form-control', 'id' => 'porcentaje_aumento', 'required' => 'required', 'step' => '0.01', 'min' => '0.01' ]) }}
                            </div>
                        </div>

                        <div class="row" style="padding:5px; text-align: center;">
                            <div class="col-md-12">
                                <button class="btn btn-success" id="btn_continuar"> <i class="fa fa-play"></i> Continuar </button>
                                {{ Form::Spin(48) }}
                            </div>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascripts')
    <script type="text/javascript">
        $(document).ready(function(){

            function formatearMoneda(valor) {
                var numero = parseFloat(valor);
                if (isNaN(numero)) {
                    numero = 0;
                }
                return numero.toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }

            function parsearMoneda(valor) {
                if (valor === null || valor === undefined) {
                    return 0;
                }
                var texto = ('' + valor).trim();
                if (texto === '') {
                    return 0;
                }
                if (texto.indexOf(',') >= 0 && texto.indexOf('.') >= 0) {
                    texto = texto.replace(/\./g, '').replace(',', '.');
                } else if (texto.indexOf(',') >= 0) {
                    texto = texto.replace(',', '.');
                }
                texto = texto.replace(/[^0-9.-]/g, '');
                var numero = parseFloat(texto);
                return isNaN(numero) ? 0 : numero;
            }

            function recalcularTotales() {
                var totalActual = 0;
                var totalNuevo = 0;

                $('#tabla_actualizacion_sueldos tbody tr').each(function(){
                    var sueldoActual = parseFloat($(this).find('.salario-actual').data('value')) || 0;
                    var sueldoNuevo = parseFloat($(this).find('.nuevo-sueldo-raw').val()) || 0;
                    totalActual += sueldoActual;
                    totalNuevo += sueldoNuevo;
                });

                $('#total_sueldo_actual').data('value', totalActual);
                $('#total_sueldo_actual').text(formatearMoneda(totalActual));
                $('#total_sueldo_nuevo').data('value', totalNuevo);
                $('#total_sueldo_nuevo').text(formatearMoneda(totalNuevo));
            }

            $('#btn_continuar').on('click', function(event){
                event.preventDefault();

                if ($('#porcentaje_aumento').val() === '') {
                    alert('Debe ingresar el porcentaje de aumento.');
                    return false;
                }

                $('#div_spin').show();
                $('#div_cargando').show();
                $('#div_resultado').html('');

                var form = $('#formulario_inicial');
                var url = form.attr('action');
                var datos = new FormData(document.getElementById('formulario_inicial'));

                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'html',
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false
                })
                .done(function(respuesta){
                    $('#div_cargando').hide();
                    $('#div_spin').hide();
                    $('#div_resultado').html(respuesta);
                    $('#div_resultado').fadeIn(1000);
                    recalcularTotales();
                });
            });

            $(document).on('input', '.input-nuevo-sueldo', function(){
                var valor = parsearMoneda($(this).val());
                $(this).closest('tr').find('.nuevo-sueldo-raw').val(valor);
                recalcularTotales();
            });

            $(document).on('blur', '.input-nuevo-sueldo', function(){
                var valor = parsearMoneda($(this).val());
                $(this).val(formatearMoneda(valor));
            });

            $(document).on('click', '.btn-remover-fila', function(event){
                event.preventDefault();
                $(this).closest('tr').remove();
                recalcularTotales();
            });

            $(document).on('click', '#btn_guardar_actualizacion', function(event){
                event.preventDefault();

                if ($('#tabla_actualizacion_sueldos tbody tr').length === 0) {
                    alert('No hay empleados para actualizar.');
                    return false;
                }

                $('#div_spin').show();
                $('#div_cargando').show();

                var form = $('#formulario_guardar');
                var url = form.attr('action');
                var datos = new FormData(document.getElementById('formulario_guardar'));

                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: 'html',
                    data: datos,
                    cache: false,
                    contentType: false,
                    processData: false
                })
                .done(function(respuesta){
                    $('#div_cargando').hide();
                    $('#div_spin').hide();
                    $('#div_resultado').html(respuesta);
                    $('#div_resultado').fadeIn(1000);
                });
            });

            $(document).on('click', '#btn_revertir_proceso', function(event){
                event.preventDefault();

                var procesoId = $(this).data('proceso');
                if (!procesoId) {
                    return false;
                }

                if (!confirm('¿Desea revertir este proceso?')) {
                    return false;
                }

                $('#div_spin').show();
                $('#div_cargando').show();

                $.ajax({
                    url: "{{ url('nomina/procesos/actualizar_sueldos/revertir') }}/" + procesoId,
                    type: 'post',
                    dataType: 'html',
                    data: {
                        _token: '{{ csrf_token() }}'
                    }
                })
                .done(function(respuesta){
                    $('#div_cargando').hide();
                    $('#div_spin').hide();
                    $('#div_resultado').html(respuesta);
                    $('#div_resultado').fadeIn(1000);
                });
            });
        });
    </script>
@endsection
