@php
    $responsables = collect();
    if ($estudiante != null) {
        $responsables = $estudiante->responsableestudiantes->unique('tercero_id');
    }
@endphp

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <strong class="pull-left">Responsables</strong>
        <span class="pull-right text-muted small">{{ $responsables->count() }} registrados</span>
    </div>
    <div class="panel-body">
        @if($responsables->isEmpty())
            <p class="text-danger"><i class="fa fa-warning"></i> Sin datos de padres ni acudiente.</p>
        @else
            <div class="row">
                @foreach($responsables as $responsable)
                    <div class="col-sm-6 col-lg-4" style="margin-bottom: 15px;">
                        <div class="panel panel-default" style="margin-bottom: 0;">
                            <div class="panel-heading text-center" style="font-weight: 600;">
                                {{ $responsable->tiporesponsable->descripcion }}
                            </div>
                            <div class="panel-body" style="padding: 10px 15px;">
                                <p><strong>Nombre:</strong> {{ $responsable->tercero->descripcion }}</p>
                                <p><strong>Cedula:</strong> {{ number_format($responsable->tercero->numero_identificacion, 0, ',', '.') }}</p>
                                <p><strong>Ocupacion:</strong> {{ $responsable->ocupacion ?: 'Sin dato' }}</p>
                                <p><strong>Telefono:</strong> {{ $responsable->tercero->telefono1 ?: 'Sin dato' }}</p>
                                <p><strong>Email:</strong> {{ $responsable->tercero->email ?: 'Sin dato' }}</p>
                                @if($responsable->tiporesponsable_id == 3)
                                    <p><strong>Empresa:</strong> {{ $responsable->empresa_labora ?: 'Sin dato' }}</p>
                                    <p><strong>Telefono trabajo:</strong> {{ $responsable->telefono_trabajo ?: 'Sin dato' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
