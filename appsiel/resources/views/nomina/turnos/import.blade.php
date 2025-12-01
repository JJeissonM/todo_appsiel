@extends('core.procesos.layout')

@section( 'titulo', 'Cargar turnos desde Excel' )

@section('detalles')
	<p>
		Este proceso carga un archivo de Excel con las fechas y horas de entradas y salidas de empleados.
        <br>
        Al presionar el botón "Subir e importar", el sistema procesará el archivo y creará o actualizará los registros de turnos correspondientes.
	</p>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">		
        
		<div class="container-fluid">
            <form action="{{ route('nomina.turnos.import.store') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="form-group">
                    <label>Archivo Excel (xlsx)</label>
                    <input type="file" name="archivo" class="form-control" required accept=".xlsx,.xls">
                    
                    {{ Form::hidden('app_id',Input::get('id')) }}
                    {{ Form::hidden('modelo_id',Input::get('id_modelo')) }}
                </div>
                <button type="submit" class="btn btn-primary">Subir e importar</button>
            </form>
        </div>
    </div>

@endsection
