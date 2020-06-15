<style>
    
</style>
<section id="parallax">
    <div class="container" id="contenedor_seccion_servicios">
        @if($parallax!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$parallax->titulo}}</h2>
            <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$parallax->descripcion}}</p>
        </div>
        <div class="row">
            
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