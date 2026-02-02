@php
    use App\Core\PasswordReset;

    $responsables = collect();
    if ($estudiante != null) {
        $responsables = $estudiante->responsableestudiantes->unique('tercero_id');
    }

    $tokens = [];
    $emails = $responsables->pluck('tercero.email')->filter();
    if (! $emails->isEmpty()) {
        $tokens = PasswordReset::whereIn('email', $emails)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('email')
            ->pluck('token', 'email')
            ->toArray();
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
                                    <p><strong>Contraseña:</strong> <span class="text-muted">{{ $tokens[$responsable->tercero->email] ?? 'Sin contraseña registrada' }}</span></p>
                                @endif
                                @if($responsable->tiporesponsable_id == 3)
                                    <p><strong>Empresa:</strong> {{ $responsable->empresa_labora ?: 'Sin dato' }}</p>
                                    <p><strong>Telefono trabajo:</strong> {{ $responsable->telefono_trabajo ?: 'Sin dato' }}</p>
                                @endif
                                @if($responsable->tiporesponsable_id == 3)
                                    <div class="text-center" style="margin-top: 10px;">
                                        @can('matriculas.estudiantes.crear_tutor')
                                            @if(!empty($responsable->tercero->email) && empty($responsable->tercero->user_id))
                                                <form method="POST" action="{{ url('matriculas/estudiantes/responsables/'.$responsable->id.'/crear_tutor') }}">
                                                    {{ csrf_field() }}
                                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                                    <button type="submit" class="btn btn-sm btn-success btn-block">Crear usuario Tutor</button>
                                                </form>
                                            @elseif(!empty($responsable->tercero->user_id))
                                                <span class="text-success">Tutor registrado</span>
                                            @else
                                                <span class="text-muted">Falta correo para crear Tutor</span>
                                            @endif
                                        @else
                                            @if(!empty($responsable->tercero->user_id))
                                                <span class="text-success">Tutor registrado</span>
                                            @elseif(!empty($responsable->tercero->email))
                                                <span class="text-muted">Sin permiso para crear usuario Tutor</span>
                                            @else
                                                <span class="text-muted">Falta correo para crear Tutor</span>
                                            @endif
                                        @endcan
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
