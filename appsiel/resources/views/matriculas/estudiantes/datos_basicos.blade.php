@php
    use Carbon\Carbon;
    $edad = null;
    if (!empty($estudiante->fecha_nacimiento)) {
        $edad = Carbon::parse($estudiante->fecha_nacimiento)->age;
    }
    $curso_actual = isset($curso_actual) ? $curso_actual : null;
    $nivel_actual = isset($nivel_actual) ? $nivel_actual : null;
    $programa_actual = isset($programa_actual) ? $programa_actual : null;
    $foto = $estudiante->imagen
        ? asset(config('configuracion.url_instancia_cliente').'/storage/app/fotos_terceros/'.$estudiante->imagen)
        : '';
    $initials = collect(explode(' ', $estudiante->nombre_completo))->filter()->map(function($word) {
        return strtoupper(substr($word, 0, 1));
    })->take(2)->implode('');
    $estado = $estudiante->estado ?? '';
    $programa = $programa_actual
        ?? ($curso_actual && $curso_actual->grado ? $curso_actual->grado->descripcion : null)
        ?? 'Programa no asignado';
    $nivel = $nivel_actual
        ?? ($curso_actual ? $curso_actual->descripcion : null)
        ?? 'Nivel no asignado';
@endphp

<style>
    .student-basic-card {
        background: #ffffff;
        border-radius: 1.1rem;
        box-shadow: 0 25px 60px rgba(12, 37, 86, 0.15);
        padding: 2.3rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e1e5f0;
    }

    .student-basic-header {
        display: flex;
        gap: 1.4rem;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .student-initials {
        width: 88px;
        height: 88px;
        border-radius: 1rem;
        background: #1f5cef;
        color: #fff;
        font-size: 2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.1em;
    }

    .student-basic-info h3 {
        margin: 0;
        font-weight: 700;
        font-size: 2.6rem;
    }

    .student-basic-info p {
        margin: 0.25rem 0;
        color: #4f5d7a;
    }

    .student-basic-badges {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
        margin-top: 0.3rem;
    }

    .student-badge {
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        background: rgba(31, 92, 214, 0.12);
        color: #1f5cef;
        text-transform: uppercase;
    }

    .student-basic-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1rem;
        background: #f7f8fd;
        border-radius: 0.9rem;
        padding: 1.1rem 1.4rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e6e8f2;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .student-basic-summary .summary-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .student-basic-summary .summary-label {
        font-size: 1.1rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6c778d;
    }

    .student-basic-summary .summary-value {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2b44;
    }

    .student-basic-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.4rem;
    }

    .student-basic-field {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .student-photo-box {
        width: 130px;
        height: 140px;
    }

    .student-photo-box .student-initials {
        width: 130px;
        height: 140px;
        border-radius: 1rem;
    }

    .student-basic-field strong {
        color: #1f2b44;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .student-basic-field span {
        color: #4f5d7a;
        font-size: 1.3rem;
    }

    .student-divider {
        border-top: 1px solid #e1e5f0;
        margin: 1.5rem 0;
    }

    .student-parents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
    }

    .student-parent-card {
        border-radius: 0.85rem;
        border: 1px solid #f1f3f7;
        padding: 1.2rem;
        background: #fbfcff;
    }

    .student-parent-card h5 {
        margin-top: 0;
        margin-bottom: 0.4rem;
        font-weight: 600;
    }

    .student-photo-holder {
        width: 130px;
        height: 140px;
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #e1e5f0;
        box-shadow: 0 8px 20px rgba(12, 37, 86, 0.15);
        object-fit: cover;
    }

    @media (max-width: 768px) {
        .student-basic-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="student-basic-card">
    <div class="student-basic-header">
        <div class="student-photo-box">
            @if($foto)
                <img src="{{ $foto }}" alt="Foto estudiante" class="student-photo-holder">
            @else
                <div class="student-initials">
                    {{ $initials ?: 'ES' }}
                </div>
            @endif
        </div>
        <div class="student-basic-info">
            <h3>{{ $estudiante->nombre_completo }}</h3>
            <div class="student-basic-badges">
                Grado: <span class="student-badge">{{ $programa }}</span>
                Curso: <span class="student-badge">{{ $nivel }}</span>
                <span class="student-badge">{{ $estado }}</span>
            </div>
            <p>{{ $estudiante->tipo_y_numero_documento_identidad }}</p>
            <p>Fecha de nacimiento: {{ $estudiante->fecha_nacimiento ?: 'Sin dato' }} @if($edad)({{ $edad }} años)@endif</p>
        </div>
    </div>

    <div class="student-basic-summary">
        <div class="summary-item">
            <span class="summary-label">Documento</span>
            <span class="summary-value">{{ $estudiante->tipo_y_numero_documento_identidad ?: 'Sin dato' }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Fecha de nacimiento</span>
            <span class="summary-value">{{ $estudiante->fecha_nacimiento ?: 'Sin dato' }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Edad</span>
            <span class="summary-value">{{ $edad ? $edad.' años' : 'Sin dato' }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Ciudad de nacimiento</span>
            <span class="summary-value">{{ $estudiante->ciudad_nacimiento ?: 'Sin dato' }}</span>
        </div>
    </div>

    <div class="student-basic-grid">
        <div class="student-basic-field">
            <strong>Género</strong>
            <span>{{ $estudiante->genero ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Dirección</strong>
            <span>{{ $estudiante->direccion1 ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Teléfono</strong>
            <span>{{ $estudiante->telefono1 ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Correo</strong>
            <span>{{ $estudiante->email ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Barrio</strong>
            <span>{{ $estudiante->barrio ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Ciudad de nacimiento</strong>
            <span>{{ $estudiante->ciudad_nacimiento ?: 'Sin dato' }}</span>
        </div>
    </div>

    <div class="student-divider"></div>

    <div class="student-basic-grid">
        <div class="student-basic-field">
            <strong>EPS</strong>
            <span>{{ $estudiante->eps ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Grupo sanguíneo</strong>
            <span>{{ $estudiante->grupo_sanguineo ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Alergias</strong>
            <span>{{ $estudiante->alergias ?: 'Sin dato' }}</span>
        </div>
        <div class="student-basic-field">
            <strong>Medicamentos</strong>
            <span>{{ $estudiante->medicamentos ?: 'Sin dato' }}</span>
        </div>
    </div>

	<div class="panel panel-default" style="border-radius: 1rem; box-shadow: 0 18px 40px rgba(15, 32, 92, 0.08); border: 0; margin-top: 1rem;">
		<div class="panel-body" style="background: #fff; border-radius: 1rem; padding: 1.5rem;">
			<h4 class="text-uppercase" style="letter-spacing: 0.08em; color: #1f2b44;">Información adicional</h4>
			<div class="row" style="margin-top: 1rem;">
				<div class="col-md-4">
					<strong>Diagnóstico</strong>
					<p>{{ $estudiante->diagnostico ?: 'Sin dato' }}</p>
				</div>
				<div class="col-md-4">
					<strong>Observaciones</strong>
					<p>{{ $estudiante->observacion_general ?: 'Sin dato' }}</p>
				</div>
				<div class="col-md-4">
					<strong>Hermanos</strong>
					<p>{{ $estudiante->numero_hermanos ?: 'Sin dato' }}</p>
				</div>
			</div>
		</div>
	</div>

    <div class="student-divider"></div>

</div>

@include('matriculas.estudiantes.datos_basicos_padres',['vista'=>$vista ?? 'show'])
