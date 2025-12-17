@extends('core.procesos.layout')

@section( 'titulo', 'Cargar turnos desde Excel' )

@section('detalles')
	<p>
		Este proceso carga un archivo de Excel con las fechas y horas de entradas y salidas de empleados.
        <br><br>
        ✅ El archivo debe tener las siguientes columnas:
        <br>
        <b>Columna D:</b> ID del empleado en el lector de huellas.
        <br>
        <b>Columna I:</b> Fecha y hora de Entrada/Salida.
        <br><br>
        ✅ Debe cargar información de la <b>fecha del día siguiente</b> al corte final para los registros de salidas.
        <br><br>
        ✅ Al presionar el botón "Subir e importar", el sistema procesará el archivo y creará o actualizará los registros de turnos correspondientes.
	</p>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">		
        
		<div class="container-fluid">
            <form id="form_turnos_import" action="{{ route('nomina.turnos.import.store') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="fecha_primer_dia">Fecha primer día:</label>
                            <div class="col-sm-9 col-md-9">
                                <input type="date" name="fecha_primer_dia" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <br>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="fecha_corte_final">Fecha corte:</label>
                            <div class="col-sm-9 col-md-9">
                                <input type="date" name="fecha_corte_final" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <br>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label col-sm-3 col-md-3" for="archivo">Archivo Excel (xlsx):</label>
                            <div class="col-sm-9 col-md-9">
                                <input type="file" name="archivo" class="form-control" required accept=".xlsx,.xls">
                            </div>
                        </div>
                    </div>
                </div>

                <br>
                    
                {{ Form::hidden('app_id',Input::get('id')) }}
                {{ Form::hidden('modelo_id',Input::get('id_modelo')) }}
                    
                <button type="submit" class="btn btn-primary">Subir e importar</button>
                <button type="button" id="btn_borrar_registros" class="btn btn-danger" title="Borrar registros entre las fechas">Borrar registros entre fechas</button>
            </form>

            <script>
                (function(){
                    var form = document.getElementById('form_turnos_import');
                    var deleteBtn = document.getElementById('btn_borrar_registros');
                    var submitBtn = form ? form.querySelector('button[type="submit"]') : null;

                    if(deleteBtn){
                        deleteBtn.addEventListener('click', function(e){
                            var fechaInicio = document.querySelector('input[name="fecha_primer_dia"]').value;
                            var fechaFin = document.querySelector('input[name="fecha_corte_final"]').value;

                            if(!fechaInicio || !fechaFin){
                                alert('Por favor ingrese ambas fechas antes de borrar los registros.');
                                return;
                            }

                            var mensaje = '¿Está seguro que desea borrar los registros entre ' + fechaInicio + ' y ' + fechaFin + '? Esta acción no se puede deshacer.';
                            if(!confirm(mensaje)){
                                return;
                            }

                            // Deshabilitar el botón y mostrar estado mientras se redirige
                            deleteBtn.disabled = true;
                            deleteBtn.setAttribute('aria-busy', 'true');
                            deleteBtn.innerHTML = 'Procesando...';

                            var base = '{{ url("nomina/turnos/borrar_registros") }}';
                            var url = base + '/' + encodeURIComponent(fechaInicio) + '/' + encodeURIComponent(fechaFin);
                            window.location.href = url;
                        });
                    }

                    if(form && submitBtn){
                        // Handle form submit to disable button after validation passes
                        form.addEventListener('submit', function(e){
                            if(!form.checkValidity()){
                                // Let browser show validation errors and do not disable
                                return;
                            }

                            // Disable submit button to prevent double submits
                            submitBtn.disabled = true;
                            submitBtn.setAttribute('aria-busy', 'true');
                            submitBtn.innerHTML = 'Procesando...';
                        });
                    }
                })();
            </script>
        </div>
    </div>

@endsection
