<div class="container-fluid">
    <h4>Actividad para la calificación {{ Input::get('columna_calificacion') }} </h4>
    <hr>
    {{ Form::open( [ 'url' => url('calificaciones_encabezados'), 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'formulario_modal'] ) }}

        <div class="form-group">
            <label for="fecha">Fecha actividad:</label>
            <input name="fecha" type="date" class="form-control" id="fecha" value="{{ $fecha }}" required="required">
        </div>

        <div class="form-group">
            <label for="descripcion"> {!! $mensaje_descripcion !!} Descripción actividad: </label>
            <textarea name="descripcion" class="form-control" id="descripcion" rows="2" required="required"> {{ $descripcion }} </textarea>
        </div>

        @if( $usar_encabezados_por_anio )
            <div class="form-group">
                <label for="titulo">Título agrupador</label>
                <input name="titulo" type="text" class="form-control" id="titulo" value="{{ $titulo }}">
            </div>

            <div class="form-group">
                <label for="label">Label del encabezado</label>
                <input name="label" type="text" class="form-control" id="label" value="{{ $label }}">
            </div>
        @endif
        
        <div class="form-group">
            <label for="fecha">Peso actividad (%)</label>
            <input name="peso" type="text" class="form-control" id="peso" value="{{ $peso }}">
            <p><b>Nota:</b> Éste campo es opcional. Si asigna un Peso, tenga en cuenta que todas las calificaciones deberán tener Peso y que todos los pesos deben sumar 100%; sino, habrá inconsitencias en el cálculo de la definitiva.</p>
        </div>
        
        <input type="hidden" name="opcion" value="{{ $opcion }}" id="opcion">
        
        <input type="hidden" name="id_encabezado_calificacion" value="{{ $id_encabezado_calificacion }}" id="id_encabezado_calificacion">
        
        <input type="hidden" name="columna_calificacion" value="{{ Input::get('columna_calificacion') }}" id="columna_calificacion">

        <input type="hidden" name="anio" value="{{ Input::get('anio') }}" id="anio">

        <input type="hidden" name="periodo_id" value="{{ Input::get('periodo_id') }}" id="periodo_id">

        <input type="hidden" name="curso_id" value="{{ Input::get('curso_id') }}" id="curso_id">

        <input type="hidden" name="asignatura_id" value="{{ Input::get('asignatura_id') }}" id="asignatura_id">

        <input type="hidden" name="creado_por" value="{{ $creado_por }}" id="creado_por">

        <input type="hidden" name="modificado_por" value="{{ $modificado_por }}" id="modificado_por">
    {{Form::close()}}
</div>
