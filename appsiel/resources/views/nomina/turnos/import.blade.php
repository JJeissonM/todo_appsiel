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
            <form action="{{ route('nomina.turnos.import.store') }}" method="POST" enctype="multipart/form-data">
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
            </form>
        </div>
    </div>

@endsection
