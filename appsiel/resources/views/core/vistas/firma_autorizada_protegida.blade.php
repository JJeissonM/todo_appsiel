<div class="firma-autorizada-vista-protegida" style="position:relative;overflow:hidden;" oncontextmenu="return false" ondragstart="return false">
    {!! $imagen !!}
    <div aria-hidden="true" style="position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none;overflow:hidden;z-index:2;">
        @foreach([8, 36, 68] as $posicion)
            <span style="position:absolute;left:{{ $posicion }}%;top:50%;transform:translateY(-50%) rotate(-22deg);font-family:Arial,sans-serif;font-size:clamp(26px,3.5vw,52px);font-weight:bold;color:rgba(0,154,205,0.65);white-space:nowrap;user-select:none;">Appsiel</span>
        @endforeach
    </div>
</div>
