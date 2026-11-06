<p>Asocie C1–C15 a esta agrupación. El porcentaje del grupo ({{ $grupo->peso }}%) se distribuye proporcionalmente según el porcentaje interno de sus columnas. Los porcentajes internos se normalizan dentro del grupo; por ejemplo, 20 y 80 representan un reparto de 20% y 80%. El label es propio de cada columna. Los porcentajes de los grupos del mismo año y periodo deben sumar 100%.</p>
<table class="table table-striped table-bordered">
    <thead><tr><th>Columna</th><th>Label</th><th>Porcentaje interno</th><th>Porcentaje de la definitiva</th><th>Acción</th></tr></thead>
    <tbody>
    @foreach($columnas as $columna)
        <tr><td>{{ $columna->columna_calificacion }}</td><td>{{ $columna->label }}</td><td>{{ $columna->porcentaje }}</td><td>{{ rtrim(rtrim(number_format($columna->peso, 6, '.', ''), '0'), '.') }}%</td>
            <td>@if(!$grupo->enUso())
                {{ Form::open(['url' => 'calificaciones/agrupaciones-columnas/'.$grupo->id.'/columnas/'.$columna->id, 'method' => 'DELETE']) }}
                <button type="submit" class="btn btn-danger btn-sm">Desasociar</button>{{ Form::close() }}
            @endif</td>
        </tr>
    @endforeach
    </tbody>
</table>
@if($grupo->enUso())
    <div class="alert alert-info">Esta agrupación ya tiene actividades o notas. Sus porcentajes y columnas se conservan para proteger las calificaciones existentes.</div>
@else
    <h4>Asociar o actualizar columna</h4>
    {{ Form::open(['url' => 'calificaciones/agrupaciones-columnas/'.$grupo->id.'/columnas']) }}
        <div class="row">
            <div class="col-md-3"><label>Columna</label><select class="form-control" name="columna_calificacion" required>
                @for($i=1;$i<=15;$i++)<option value="C{{$i}}">C{{$i}}</option>@endfor
            </select></div>
            <div class="col-md-4"><label>Label</label><input name="label" class="form-control" maxlength="60" required></div>
            <div class="col-md-3"><label>Porcentaje interno</label><input name="porcentaje" type="number" min="0.0001" max="100" step="0.0001" class="form-control" required></div>
            <div class="col-md-2"><br><button class="btn btn-primary" type="submit">Guardar</button></div>
        </div>
    {{ Form::close() }}
@endif
