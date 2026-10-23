<style>
    @page { margin: 35px 65px 140px; }
    .paz-salvo { font-family: "Times New Roman", serif; font-size: 12pt; line-height: 1.4; color: #000; }
    .paz-salvo-banner { text-align: center; height: 135px; }
    .paz-salvo-banner img { max-width: 100%; max-height: 130px; }
    .paz-salvo h2 { text-align: center; font-size: 15pt; margin: 40px 0 45px; }
    .paz-salvo p { text-align: justify; margin: 0 0 35px; }
    .paz-salvo-firma { margin-top: 105px; }
    .paz-salvo-footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 10pt; }
    .paz-salvo img { padding: 0; }
    .paz-salvo-footer img { width: 100%; max-height: 125px; }
</style>
<div class="paz-salvo">
    <div class="paz-salvo-footer">
        @if(config('matriculas.footer_reportes') == 'imagen')
            <img src="{{ config('matriculas.url_imagen_footer') }}" alt="Pie de página">
        @else
            <hr>
            <div>{{ $colegio->direccion }} · {{ $colegio->telefonos }}</div>
            <div>{{ $colegio->ciudad }}</div>
        @endif
    </div>
    <div class="paz-salvo-banner">
        @if(config('matriculas.banner_reportes') == 'imagen')
            <img src="{{ config('matriculas.url_imagen_banner') }}" alt="Encabezado institucional">
        @else
            <strong style="font-size: 20pt;">{{ $colegio->descripcion }}</strong><br>
            {!! $colegio->resolucion !!}<br>
            {{ $colegio->ciudad }}
        @endif
    </div>
    <h2>PAZ Y SALVO.</h2>
    <p><strong>{{ mb_strtoupper($colegio->descripcion, 'UTF-8') }}</strong>, hace constar que el estudiante
        <strong>{{ mb_strtoupper($estudiante->nombre_completo, 'UTF-8') }}</strong>, identificado con
        {{ $tipo_documento }} número {{ $estudiante->numero_identificacion }}, {{ $texto_cumplimiento }}</p>
    <p>Por lo tanto, se expide el presente paz y salvo a los {{ $array_fecha[0] }} días del mes de
        {{ $array_fecha[1] }} de {{ $array_fecha[2] }}, para los fines que el interesado estime convenientes.</p>
    <div class="paz-salvo-firma">
        Cordialmente,<br><br><br>
        <strong>{{ mb_strtoupper($firmante->tercero_nombre, 'UTF-8') }}</strong><br>
        {{ $firma->titulo_tercero }}
    </div>
</div>
