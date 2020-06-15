<style>
    .parallax {
        position: relative;
        @if($parallax->modo=='COLOR') 
            background-color: {{ $parallax->fondo }};
        @else 
            background: url("img/parallax/{{$parallax->fondo}}") no-repeat fixed;
        @endif 
        background-position: center center;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
        min-height: 500px;
    }

    
</style>
<section id="parallax" class="parallax">
    <div class="container" id="contenedor_seccion_servicios">
        @if($parallax!=null)
        <div class="section-header" style="color: {{$parallax->textcolor}}; font-weight: bold;">
            <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown; color: {{$parallax->textcolor}}; font-weight: bold;">{{$parallax->titulo}}</h2>
            <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$parallax->descripcion}}</p>
        </div>
        <div class="row">
            <div class="col-md-12" style="color: {{$parallax->textcolor}};">
                {!!$parallax->content_html!!}
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
</section>
<script type="text/javascript">

</script>