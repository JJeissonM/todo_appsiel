<style>
    .parallax {        
        min-height: 500px;        
        background-repeat: repeat-y;
        background-position-x: center;
    }

    
</style>
<section id="parallax" class="parallax p-md-5 p-sm-2" style="@if($parallax!=null) @if($parallax->modo=='COLOR') background-color: {{ $parallax->fondo }}; @else background: url('img/parallax/{{$parallax->fondo}}') repeat-y center 0; background-size: auto 100%; @endif @endif" >
    <div class="container h-100">
        <div class="row flex-column justify-content-between align-items-center h-100 " id="contenedor_seccion_servicios">
        @if($parallax!=null)
            <div class="section-header mb-2" style="color: {{$parallax->textcolor}}; font-weight: bold;">
                <h2 class="section-title text-center wow fadeInDown animated text" style="visibility: visible; animation-name: fadeInDown; color: {{$parallax->textcolor}}; font-weight: bold;  text-shadow: 3px 3px 5px #777777;">{{$parallax->titulo}}</h2>
                <p class="text-center wow fadeInDown animated h3" style="color: {{$parallax->textcolor}}; text-shadow: 2px 2px 5px #777777; visibility: visible; animation-name: fadeInDown;">{{$parallax->descripcion}}</p>
            </div>
            <div class="row">
                <div class="col-md-12 text-center " style="color: {{$parallax->textcolor}}; text-shadow: 1px 1px 4px #777777;">
                    <big>{!!$parallax->content_html!!}</big>
                </div>
            </div>
            <!--/.row-->
        @else
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown">Sección</h2>
                <p class="text-center wow fadeInDown">Sin configuración</p>
            </div>
        @endif
    </div>
    </div>
    
</section>
<script type="text/javascript">

</script>