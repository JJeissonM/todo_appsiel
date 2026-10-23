<div class="seccion-ficha">DATOS BÁSICOS DEL ESTUDIANTE</div>
<div class="ficha-alumno">
        <div class="ficha-texto">
            <div><span class="etiqueta-ficha">{{ config('calificaciones.etiqueta_estudiante', 'Estudiante') }}</span><br><strong>{{ $estudiante->nombre_completo }}</strong></div>
            <div><span class="etiqueta-ficha">{{ config('calificaciones.etiqueta_curso', 'Curso') }}:</span> {{ $curso_label }}</div>
            <div><span class="etiqueta-ficha">Documento de identidad:</span> {{ $estudiante->tipo_y_numero_documento_identidad }}</div>
            <div><span class="etiqueta-ficha">Fecha de nacimiento:</span> {{ $estudiante->fecha_nacimiento }}
                @if(!empty($estudiante->fecha_nacimiento))
                    ({{ (new DateTime($estudiante->fecha_nacimiento))->diff(new DateTime('today'))->y }} años)
                @endif
            </div>
            <div><span class="etiqueta-ficha">Género:</span> {{ $estudiante->genero }}</div>
            <div><span class="etiqueta-ficha">Dirección:</span> {{ $estudiante->direccion1 }}</div>
            <div><span class="etiqueta-ficha">Teléfono:</span> {{ $estudiante->telefono1 }}</div>
            <div><span class="etiqueta-ficha">Correo electrónico:</span> {{ $estudiante->email }}</div>
        </div>
        <div class="foto-alumno">
            @if(!empty($foto_estudiante_pdf))
                <img src="{{ $foto_estudiante_pdf }}" alt="Fotografía del estudiante">
            @else
                <div style="padding: 35px 5px; border: 1px solid #cbd2d8; color: #657580; font-size: 8pt;">Sin fotografía</div>
            @endif
        </div>
</div>
