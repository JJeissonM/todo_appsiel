@if(isset($revertido) && $revertido)
    <div class="alert alert-success">
        <strong>Proceso revertido.</strong> Se restauraron {{ $cantidad }} contratos del proceso #{{ $proceso->id }}.
    </div>
@else
    <div class="alert alert-success">
        <strong>Proceso aplicado.</strong> Se actualizaron {{ $cantidad }} contratos. Proceso #{{ $proceso->id }}.
    </div>
@endif

<div class="row" style="padding:5px;">
    <div class="col-md-12">
        <div class="well">
            <p><strong>Estado:</strong> {{ $proceso->estado }}</p>
            <p><strong>Fecha:</strong> {{ $proceso->fecha }}</p>
            <p><strong>Porcentaje:</strong> {{ number_format($proceso->porcentaje, 2, ',', '.') }}%</p>
            @if(!is_null($proceso->grupo_empleado_id))
                <?php $grupo = App\Nomina\GrupoEmpleado::find($proceso->grupo_empleado_id); ?>
                <p><strong>Grupo empleado:</strong> {{ $grupo ? $grupo->descripcion : 'Sin definir' }}</p>
            @else
                <p><strong>Grupo empleado:</strong> Todos</p>
            @endif
        </div>
    </div>
</div>

@if($proceso->estado != 'Revertido')
    <div class="row" style="padding:5px; text-align: center;">
        <div class="col-md-12">
            <button class="btn btn-warning" id="btn_revertir_proceso" data-proceso="{{ $proceso->id }}"> <i class="fa fa-undo"></i> Revertir proceso </button>
        </div>
    </div>
@endif
